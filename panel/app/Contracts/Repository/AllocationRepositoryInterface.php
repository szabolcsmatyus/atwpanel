<?php

namespace Pterodactyl\Contracts\Repository;

use Illuminate\Support\Collection;

interface AllocationRepositoryInterface extends RepositoryInterface
{
    /**
     * Return all of the allocations that exist for a node that are not currently
     * allocated.
     *
     * @param int $node
     * @return array
     */
    public function getUnassignedAllocationIds(int $node): array;

    /**
     * Return a single allocation from those meeting the requirements.
     *
     * @param array $nodes
     * @param array $ports
     * @param bool $dedicated
     * @return \Pterodactyl\Models\Allocation|null
     */
    public function getRandomAllocation(array $nodes, array $ports, bool $dedicated = false);
}
