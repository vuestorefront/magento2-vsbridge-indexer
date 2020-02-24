<?php

declare(strict_types=1);

/**
 * @package  Divante\VsbridgeIndexerCore
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Cache;

/**
 * Interface ConfigInterface
 */
interface ConfigInterface
{
    /**
     * XML PATH Prefix for redis cache settings
     */
    const CACHE_SETTINGS_XML_PREFIX = 'vsbridge_indexer_settings/redis_cache_settings';

    const CLEAR_CACHE_FIELD = 'clear_cache';
    const VSF_BASE_URL_FIELD = 'vsf_base_url';
    const INVALIDATE_CACHE_FIELD = 'invalidate_cache_key';
    const CONNECTION_TIMEOUT_FIELD = 'connection_timeout';

    const INVALIDATE_CACHE_ENTITIES_BATCH_SIZE_FIELD = 'entity_invalidate_batch_size';

    /**
     * @param int $storeId
     *
     * @return bool
     */
    public function clearCache($storeId): bool;

    /**
     * @param int $storeId
     *
     * @return int
     */
    public function getInvalidateEntitiesBatchSize(int $storeId): int;

    /**
     * @param int $storeId
     *
     * @return string
     */
    public function getVsfBaseUrl($storeId): string;

    /**
     * @param int $storeId
     *
     * @return string
     */
    public function getInvalidateCacheKey($storeId): string;

    /**
     * @param int $storeId
     *
     * @return int
     */
    public function getTimeout($storeId): int;
    /**
     * @param int $storeId
     *
     * @return array
     */
    public function getConnectionOptions($storeId): array;
}
