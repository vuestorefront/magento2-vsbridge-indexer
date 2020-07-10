<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Api;

use Magento\Store\Api\Data\StoreInterface;

/**
 * Interface IndexOperationInterface
 */
interface IndexOperationInterface
{
    /**
     * @param int $storeId
     * @param BulkRequestInterface $bulk
     *
     * @return BulkResponseInterface
     */
    public function executeBulk($storeId, BulkRequestInterface $bulk);

    /**
     * @param int $storeId
     * @param array $params
     *
     * @return void
     */
    public function deleteByQuery($storeId, array $params);

    /**
     * @param int $storeId
     * @param string $indexName
     *
     * @return bool
     */
    public function indexExists($storeId, $indexName);

    /**
     * @param string $indexIdentifier
     * @param StoreInterface  $store
     *
     * @return IndexInterface
     */
    public function getIndexByName($indexIdentifier, StoreInterface $store);

    /**
     * @param StoreInterface  $store
     *
     * @return string
     */
    public function getIndexAlias(StoreInterface $store);

    /**
     * @param string $indexIdentifier
     * @param StoreInterface  $store
     *
     * @return IndexInterface
     */
    public function createIndex($indexIdentifier, StoreInterface $store);

    /**
     * @param int $storeId
     * @param IndexInterface $index
     *
     * @return void
     */
    public function refreshIndex($storeId, IndexInterface $index);

    /**
     * @param int $storeId
     * @param string $indexName
     * @param string $indexAlias
     *
     * @return void
     */
    public function switchIndexer($storeId, string $indexName, string $indexAlias);

    /**
     * @return BulkRequestInterface
     */
    public function createBulk();

    /**
     * @return int
     */
    public function getBatchIndexingSize();

    /**
     * @param int $storeId
     * @param string $indexName
     *
     * @return void
     */
    public function optimizeEsIndexing($storeId, string $indexName);

    /**
     * @param int $storeId
     * @param string $indexName
     *
     * @return void
     */
    public function cleanAfterOptimizeEsIndexing($storeId, string $indexName);
}
