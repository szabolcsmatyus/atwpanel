<?php

use Illuminate\Database\Seeder;
use Pterodactyl\Services\Nests\NestCreationService;
use Pterodactyl\Contracts\Repository\NestRepositoryInterface;

class NestSeeder extends Seeder
{
    /**
     * @var \Pterodactyl\Services\Nests\NestCreationService
     */
    private $creationService;

    /**
     * @var \Pterodactyl\Contracts\Repository\NestRepositoryInterface
     */
    private $repository;

    /**
     * NestSeeder constructor.
     *
     * @param \Pterodactyl\Services\Nests\NestCreationService           $creationService
     * @param \Pterodactyl\Contracts\Repository\NestRepositoryInterface $repository
     */
    public function __construct(
        NestCreationService $creationService,
        NestRepositoryInterface $repository
    ) {
        $this->creationService = $creationService;
        $this->repository = $repository;
    }

    /**
     * Run the seeder to add missing nests to the Panel.
     *
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     */
    public function run()
    {
        $items = $this->repository->findWhere([
            'author' => 'support@pterodactyl.io',
        ])->keyBy('name')->toArray();

        $this->createMinecraftNest(array_get($items, 'Minecraft'));
        $this->createGamesNest(array_get($items, 'Games'));
        $this->createVoiceServersNest(array_get($items, 'Voice Servers'));
        $this->createTerrariaNest(array_get($items, 'Terraria'));
        $this->createGTANest(array_get($items, 'GTA'));
    }
    /**
     * Create the Minecraft nest to be used later on.
     *
     * @param array|null $nest
     *
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     */
    private function createGTANest(array $nest = null)
    {
        if (is_null($nest)) {
            $this->creationService->handle([
                'name' => 'GTA',
                'description' => 'GTA - the classic game from Mojang. With support for Vanilla MC, Spigot, and many others!',
            ], 'support@pterodactyl.io');
        }
    }
    /**
     * Create the Minecraft nest to be used later on.
     *
     * @param array|null $nest
     *
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     */
    private function createMinecraftNest(array $nest = null)
    {
        if (is_null($nest)) {
            $this->creationService->handle([
                'name' => 'Minecraft',
                'description' => 'Minecraft - the classic game from Mojang. With support for Vanilla MC, Spigot, and many others!',
            ], 'support@pterodactyl.io');
        }
    }

    /**
     * Create the Source Engine Games nest to be used later on.
     *
     * @param array|null $nest
     *
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     */
    private function createGamesNest(array $nest = null)
    {
        if (is_null($nest)) {
            $this->creationService->handle([
                'name' => 'Games',
                'description' => 'Games',
            ], 'support@pterodactyl.io');
        }
    }

    /**
     * Create the Voice Servers nest to be used later on.
     *
     * @param array|null $nest
     *
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     */
    private function createVoiceServersNest(array $nest = null)
    {
        if (is_null($nest)) {
            $this->creationService->handle([
                'name' => 'Voice Servers',
                'description' => 'Voice servers such as Mumble and Teamspeak 3.',
            ], 'support@pterodactyl.io');
        }
    }

    /**
     * Create the Rust nest to be used later on.
     *
     * @param array|null $nest
     *
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     */
    private function createTerrariaNest(array $nest = null)
    {
        if (is_null($nest)) {
            $this->creationService->handle([
                'name' => 'Terraria',
                'description' => 'Terraria',
            ], 'support@pterodactyl.io');
        }
    }
}
