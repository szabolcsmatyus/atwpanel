<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Pterodactyl\Models\Server;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\Allocation;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Repositories\Eloquent\ServerRepository;
use Pterodactyl\Repositories\Eloquent\AllocationRepository;
use Pterodactyl\Transformers\Api\Client\AllocationTransformer;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\Http\Requests\Api\Client\Servers\Network\GetNetworkRequest;
use Pterodactyl\Http\Requests\Api\Client\Servers\Network\DeleteAllocationRequest;
use Pterodactyl\Http\Requests\Api\Client\Servers\Network\UpdateAllocationRequest;
use Pterodactyl\Http\Requests\Api\Client\Servers\Network\SetPrimaryAllocationRequest;

class NetworkAllocationController extends ClientApiController
{
    /**
     * @var \Pterodactyl\Repositories\Eloquent\AllocationRepository
     */
    private $repository;

    /**
     * @var \Pterodactyl\Repositories\Eloquent\ServerRepository
     */
    private $serverRepository;

    /**
     * NetworkController constructor.
     *
     * @param \Pterodactyl\Repositories\Eloquent\AllocationRepository $repository
     * @param \Pterodactyl\Repositories\Eloquent\ServerRepository $serverRepository
     */
    public function __construct(
        AllocationRepository $repository,
        ServerRepository $serverRepository
    ) {
        parent::__construct();

        $this->repository = $repository;
        $this->serverRepository = $serverRepository;
    }

    /**
     * Lists all of the allocations available to a server and wether or
     * not they are currently assigned as the primary for this server.
     *
     * @param \Pterodactyl\Http\Requests\Api\Client\Servers\Network\GetNetworkRequest $request
     * @param \Pterodactyl\Models\Server $server
     * @return array
     */
    public function index(GetNetworkRequest $request, Server $server): array
    {
        return $this->fractal->collection($server->allocations)
            ->transformWith($this->getTransformer(AllocationTransformer::class))
            ->toArray();
    }

    /**
     * Set the primary allocation for a server.
     *
     * @param \Pterodactyl\Http\Requests\Api\Client\Servers\Network\UpdateAllocationRequest $request
     * @param \Pterodactyl\Models\Server $server
     * @param \Pterodactyl\Models\Allocation $allocation
     * @return array
     *
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     * @throws \Pterodactyl\Exceptions\Repository\RecordNotFoundException
     */
    public function update(UpdateAllocationRequest $request, Server $server, Allocation $allocation): array
    {
        $allocation = $this->repository->update($allocation->id, [
            'notes' => $request->input('notes'),
        ]);

        return $this->fractal->item($allocation)
            ->transformWith($this->getTransformer(AllocationTransformer::class))
            ->toArray();
    }

    /**
     * Set the primary allocation for a server.
     *
     * @param \Pterodactyl\Http\Requests\Api\Client\Servers\Network\SetPrimaryAllocationRequest $request
     * @param \Pterodactyl\Models\Server $server
     * @param \Pterodactyl\Models\Allocation $allocation
     * @return array
     *
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     * @throws \Pterodactyl\Exceptions\Repository\RecordNotFoundException
     */
    public function setPrimary(SetPrimaryAllocationRequest $request, Server $server, Allocation $allocation): array
    {
        $this->serverRepository->update($server->id, ['allocation_id' => $allocation->id]);

        return $this->fractal->item($allocation)
            ->transformWith($this->getTransformer(AllocationTransformer::class))
            ->toArray();
    }

    /**
     * Delete an allocation from a server.
     *
     * @param \Pterodactyl\Http\Requests\Api\Client\Servers\Network\DeleteAllocationRequest $request
     * @param \Pterodactyl\Models\Server $server
     * @param \Pterodactyl\Models\Allocation $allocation
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Pterodactyl\Exceptions\DisplayException
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     * @throws \Pterodactyl\Exceptions\Repository\RecordNotFoundException
     */
    public function delete(DeleteAllocationRequest $request, Server $server, Allocation $allocation)
    {
        if ($allocation->id === $server->allocation_id) {
            throw new DisplayException(
                'Cannot delete the primary allocation for a server.'
            );
        }

        $this->repository->update($allocation->id, ['server_id' => null, 'notes' => null]);

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }
}
