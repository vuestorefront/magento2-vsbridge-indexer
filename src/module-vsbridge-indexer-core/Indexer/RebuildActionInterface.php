<?php

declare(strict_types=1);

namespace Divante\VsbridgeIndexerCore\Indexer;

/**
 * Rebuild Action Interface
 *
 * @api
 */
interface RebuildActionInterface
{
    /**
     * @param int $storeId
     * @param array $ids
     *
     * @return \Traversable
     */
    public function rebuild(int $storeId, array $ids): \Traversable;
}
