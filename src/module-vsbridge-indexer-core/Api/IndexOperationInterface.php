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
     * @param BulkRequestInterface $bulk
     *
     * @return BulkResponseInterface
     */
    public function executeBulk(BulkRequestInterface $bulk);

    /**
     * @param array $params
     *
     * @return void
     */
    public function deleteByQuery(array $params);

    /**
     * @param string $indexName
     *
     * @return bool
     */
    public function indexExists($indexName);

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
     * @param IndexInterface $index
     *
     * @return void
     */
    public function refreshIndex(IndexInterface $index);

    /**
     * @param string $indexName
     * @param string $indexAlias
     *
     * @return void
     */
    public function switchIndexer(string $indexName, string $indexAlias);

    /**
     * @return BulkRequestInterface
     */
    public function createBulk();

    /**
     * @return int
     */
    public function getBatchIndexingSize();
}
