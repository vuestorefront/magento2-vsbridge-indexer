<?php declare(strict_types=1);

namespace Divante\VsbridgeIndexerCore\System;

/**
 * Interface GeneralConfigInterface
 */
interface GeneralConfigInterface
{
    /**
     * Indexer enabled config path
     */
    const XML_PATH_GENERAL_INDEXER_ENABLED = 'vsbridge_indexer_settings/general_settings/enable';

    /**
     * Allowed stores to reindex config path
     */
    const XML_PATH_ALLOWED_STORES_TO_REINDEX = 'vsbridge_indexer_settings/general_settings/allowed_stores';

    /**
     * Check if store can be reindex
     *
     * @param int $storeId
     *
     * @return bool
     */
    public function canReindexStore($storeId): bool;

    /**
     * Get Store ids allowed to reindex
     *
     * @return array
     */
    public function getStoresToIndex(): array;

    /**
     * Check if ES indexing enabled
     *
     * @return bool
     */
    public function isEnabled(): bool;
}
