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
            'author' => 'info@game.atw.hu',
        ])->keyBy('name')->toArray();

        $this->createMinecraftNest(array_get($items, 'Minecraft'));
        $this->createSourceEngineNest(array_get($items, 'Source Engine'));
        $this->createVoiceServersNest(array_get($items, 'Voice Servers'));
        $this->createRustNest(array_get($items, 'Rust'));
        $this->createTerrariaNest(array_get($items, 'Terraria'));
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
            ], 'info@game.atw.hu');
        }
    }

    /**
     * Create the Source Engine Games nest to be used later on.
     *
     * @param array|null $nest
     *
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     */
    private function createSourceEngineNest(array $nest = null)
    {
        if (is_null($nest)) {
            $this->creationService->handle([
                'name' => 'Source Engine',
                'description' => 'Includes support for most Source Dedicated Server games.',
            ], 'info@game.atw.hu');
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
            ], 'info@game.atw.hu');
        }
    }

    /**
     * Create the Rust nest to be used later on.
     *
     * @param array|null $nest
     *
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     */
    private function createRustNest(array $nest = null)
    {
        if (is_null($nest)) {
            $this->creationService->handle([
                'name' => 'Rust',
                'description' => 'Rust - A game where you must fight to survive.',
            ], 'info@game.atw.hu');
        }
    }
    /**
     * Create the Terraria nest to be used later on.
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
                'description' => 'Terraria ',
            ], 'info@game.atw.hu');
        }
    }
}
