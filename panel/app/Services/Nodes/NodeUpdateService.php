<?php

namespace Pterodactyl\Services\Nodes;

use Illuminate\Support\Str;
use Pterodactyl\Models\Node;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Contracts\Encryption\Encrypter;
use Pterodactyl\Repositories\Eloquent\NodeRepository;
use Pterodactyl\Repositories\Daemon\ConfigurationRepository;
use Pterodactyl\Repositories\Wings\DaemonConfigurationRepository;
use Pterodactyl\Exceptions\Http\Connection\DaemonConnectionException;
use Pterodactyl\Exceptions\Service\Node\ConfigurationNotPersistedException;

class NodeUpdateService
{
    /**
     * @var \Illuminate\Database\ConnectionInterface
     */
    private $connection;

    /**
     * @var \Pterodactyl\Repositories\Wings\DaemonConfigurationRepository
     */
    private $configurationRepository;

    /**
     * @var \Illuminate\Contracts\Encryption\Encrypter
     */
    private $encrypter;

    /**
     * @var \Pterodactyl\Repositories\Eloquent\NodeRepository
     */
    private $repository;

    /**
     * UpdateService constructor.
     *
     * @param \Illuminate\Database\ConnectionInterface $connection
     * @param \Illuminate\Contracts\Encryption\Encrypter $encrypter
     * @param \Pterodactyl\Repositories\Wings\DaemonConfigurationRepository $configurationRepository
     * @param \Pterodactyl\Repositories\Eloquent\NodeRepository $repository
     */
    public function __construct(
        ConnectionInterface $connection,
        Encrypter $encrypter,
        DaemonConfigurationRepository $configurationRepository,
        NodeRepository $repository
    ) {
        $this->connection = $connection;
        $this->configurationRepository = $configurationRepository;
        $this->encrypter = $encrypter;
        $this->repository = $repository;
    }

    /**
     * Update the configuration values for a given node on the machine.
     *
     * @param \Pterodactyl\Models\Node $node
     * @param array $data
     * @param bool $resetToken
     *
     * @return \Pterodactyl\Models\Node
     * @throws \Throwable
     */
    public function handle(Node $node, array $data, bool $resetToken = false)
    {
        if ($resetToken) {
            $data['daemon_token'] = $this->encrypter->encrypt(Str::random(Node::DAEMON_TOKEN_LENGTH));
            $data['daemon_token_id'] = Str::random(Node::DAEMON_TOKEN_ID_LENGTH);
        }

        [$updated, $exception] = $this->connection->transaction(function () use ($data, $node) {
            /** @var \Pterodactyl\Models\Node $updated */
            $updated = $this->repository->withFreshModel()->update($node->id, $data, true, true);

            try {
                // If we're changing the FQDN for the node, use the newly provided FQDN for the connection
                // address. This should alleviate issues where the node gets pointed to a "valid" FQDN that
                // isn't actually running the daemon software, and therefore you can't actually change it
                // back.
                //
                // This makes more sense anyways, because only the Panel uses the FQDN for connecting, the
                // node doesn't actually care about this.
                //
                // @see https://github.com/pterodactyl/panel/issues/1931
                $node->fqdn = $updated->fqdn;

                $this->configurationRepository->setNode($node)->update($updated);
            } catch (DaemonConnectionException $exception) {
                if (! is_null($exception->getPrevious()) && $exception->getPrevious() instanceof ConnectException) {
                    return [$updated, true];
                }

                throw $exception;
            }

            return [$updated, false];
        });

        if ($exception) {
            throw new ConfigurationNotPersistedException(trans('exceptions.node.daemon_off_config_updated'));
        }

        return $updated;
    }
}
