<?php

namespace Pterodactyl\Http\Controllers\Api\Remote\Servers;

use Cake\Chronos\Chronos;
use Lcobucci\JWT\Builder;
use Illuminate\Http\Request;
use Lcobucci\JWT\Signer\Key;
use Psr\Log\LoggerInterface;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Illuminate\Database\ConnectionInterface;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Servers\SuspensionService;
use Pterodactyl\Repositories\Eloquent\NodeRepository;
use Pterodactyl\Repositories\Eloquent\ServerRepository;
use Pterodactyl\Repositories\Wings\DaemonServerRepository;
use Pterodactyl\Repositories\Wings\DaemonTransferRepository;
use Pterodactyl\Contracts\Repository\AllocationRepositoryInterface;
use Pterodactyl\Exceptions\Http\Connection\DaemonConnectionException;
use Pterodactyl\Services\Servers\ServerConfigurationStructureService;

class ServerTransferController extends Controller
{
    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    private $connection;

    /**
     * @var \Pterodactyl\Repositories\Eloquent\ServerRepository
     */
    private $repository;

    /**
     * @var \Pterodactyl\Contracts\Repository\AllocationRepositoryInterface
     */
    private $allocationRepository;

    /**
     * @var \Pterodactyl\Repositories\Eloquent\NodeRepository
     */
    private $nodeRepository;

    /**
     * @var \Pterodactyl\Repositories\Wings\DaemonServerRepository
     */
    private $daemonServerRepository;

    /**
     * @var \Pterodactyl\Repositories\Wings\DaemonTransferRepository
     */
    private $daemonTransferRepository;

    /**
     * @var \Pterodactyl\Services\Servers\ServerConfigurationStructureService
     */
    private $configurationStructureService;

    /**
     * @var \Pterodactyl\Services\Servers\SuspensionService
     */
    private $suspensionService;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $writer;

    /**
     * ServerTransferController constructor.
     *
     * @param \Illuminate\Database\ConnectionInterface $connection
     * @param \Pterodactyl\Repositories\Eloquent\ServerRepository $repository
     * @param \Pterodactyl\Contracts\Repository\AllocationRepositoryInterface $allocationRepository
     * @param \Pterodactyl\Repositories\Eloquent\NodeRepository $nodeRepository
     * @param \Pterodactyl\Repositories\Wings\DaemonServerRepository $daemonServerRepository
     * @param \Pterodactyl\Repositories\Wings\DaemonTransferRepository $daemonTransferRepository
     * @param \Pterodactyl\Services\Servers\ServerConfigurationStructureService $configurationStructureService
     * @param \Pterodactyl\Services\Servers\SuspensionService $suspensionService
     * @param \Psr\Log\LoggerInterface $writer
     */
    public function __construct(
        ConnectionInterface $connection,
        ServerRepository $repository,
        AllocationRepositoryInterface $allocationRepository,
        NodeRepository $nodeRepository,
        DaemonServerRepository $daemonServerRepository,
        DaemonTransferRepository $daemonTransferRepository,
        ServerConfigurationStructureService $configurationStructureService,
        SuspensionService $suspensionService,
        LoggerInterface $writer
    ) {
        $this->connection = $connection;
        $this->repository = $repository;
        $this->allocationRepository = $allocationRepository;
        $this->nodeRepository = $nodeRepository;
        $this->daemonServerRepository = $daemonServerRepository;
        $this->daemonTransferRepository = $daemonTransferRepository;
        $this->configurationStructureService = $configurationStructureService;
        $this->suspensionService = $suspensionService;
        $this->writer = $writer;
    }

    /**
     * The daemon notifies us about the archive status.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $uuid
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Pterodactyl\Exceptions\Http\Connection\DaemonConnectionException
     * @throws \Pterodactyl\Exceptions\Repository\RecordNotFoundException
     * @throws \Throwable
     */
    public function archive(Request $request, string $uuid)
    {
        $server = $this->repository->getByUuid($uuid);

        // Unsuspend the server and don't continue the transfer.
        if (! $request->input('successful')) {
            $this->suspensionService->toggle($server, 'unsuspend');

            return JsonResponse::create([], Response::HTTP_NO_CONTENT);
        }

        $server->node_id = $server->transfer->new_node;

        $data = $this->configurationStructureService->handle($server);
        $data['suspended'] = false;
        $data['service']['skip_scripts'] = true;

        $allocations = $server->getAllocationMappings();
        $data['allocations']['default']['ip'] = array_key_first($allocations);
        $data['allocations']['default']['port'] = $allocations[$data['allocations']['default']['ip']][0];

        $now = Chronos::now();
        $signer = new Sha256;

        $token = (new Builder)->issuedBy(config('app.url'))
            ->permittedFor($server->node->getConnectionAddress())
            ->identifiedBy(hash('sha256', $server->uuid), true)
            ->issuedAt($now->getTimestamp())
            ->canOnlyBeUsedAfter($now->getTimestamp())
            ->expiresAt($now->addMinutes(15)->getTimestamp())
            ->relatedTo($server->uuid, true)
            ->getToken($signer, new Key($server->node->getDecryptedKey()));

        // On the daemon transfer repository, make sure to set the node after the server
        // because setServer() tells the repository to use the server's node and not the one
        // we want to specify.
        try {
            $this->daemonTransferRepository
                ->setServer($server)
                ->setNode($this->nodeRepository->find($server->transfer->new_node))
                ->notify($server, $data, $server->node, $token->__toString());
        } catch (DaemonConnectionException $exception) {
            throw $exception;
        }

        return JsonResponse::create([], Response::HTTP_NO_CONTENT);
    }

    /**
     * The daemon notifies us about a transfer failure.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $uuid
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function failure(string $uuid)
    {
        $server = $this->repository->getByUuid($uuid);
        $transfer = $server->transfer;

        $allocationIds = json_decode($transfer->new_additional_allocations);
        array_push($allocationIds, $transfer->new_allocation);

        // Remove the new allocations.
        $this->allocationRepository->updateWhereIn('id', $allocationIds, ['server_id' => null]);

        // Unsuspend the server.
        $this->suspensionService->toggle($server, 'unsuspend');

        return JsonResponse::create([], Response::HTTP_NO_CONTENT);
    }

    /**
     * The daemon notifies us about a transfer success.
     *
     * @param string $uuid
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function success(string $uuid)
    {
        $server = $this->repository->getByUuid($uuid);
        $transfer = $server->transfer;

        $allocationIds = json_decode($transfer->old_additional_allocations);
        array_push($allocationIds, $transfer->old_allocation);

        // Begin a transaction.
        $this->connection->beginTransaction();

        // Remove the old allocations.
        $this->allocationRepository->updateWhereIn('id', $allocationIds, ['server_id' => null]);

        // Update the server's allocation_id and node_id.
        $server->allocation_id = $transfer->new_allocation;
        $server->node_id = $transfer->new_node;
        $server->save();

        // Mark the transfer as successful.
        $transfer->successful = true;
        $transfer->save();

        // Commit the transaction.
        $this->connection->commit();

        // Delete the server from the old node
        try {
            $this->daemonServerRepository->setServer($server)->delete();
        } catch (DaemonConnectionException $exception) {
            $this->writer->warning($exception);
        }

        // Unsuspend the server
        $server->load('node');
        $this->suspensionService->toggle($server, $this->suspensionService::ACTION_UNSUSPEND);

        return JsonResponse::create([], Response::HTTP_NO_CONTENT);
    }
}
