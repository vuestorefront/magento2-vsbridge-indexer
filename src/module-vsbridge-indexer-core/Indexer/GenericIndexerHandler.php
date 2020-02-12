<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Indexer;

use Divante\VsbridgeIndexerCore\Api\BulkLoggerInterface;
use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeIndexerCore\Api\IndexInterface;
use Divante\VsbridgeIndexerCore\Api\Index\IndexOperationProviderInterface;
use Divante\VsbridgeIndexerCore\Api\Indexer\TransactionKeyInterface;
use Divante\VsbridgeIndexerCore\Api\IndexOperationInterface;
use Divante\VsbridgeIndexerCore\Exception\ConnectionDisabledException;
use Divante\VsbridgeIndexerCore\Model\IndexerRegistry;
use Exception;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Store\Api\Data\StoreInterface;
use Traversable;

/**
 * Class IndexerHandler
 *
 * TODO refactor - coupling between objects
 */
class GenericIndexerHandler
{
    /**
     * @var Batch
     */
    private $batch;

    /**
     * @var IndexOperationProviderInterface
     */
    private $indexOperationProvider;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var string
     */
    private $typeName;

    /**
     * @var string
     */
    private $indexIdentifier;

    /**
     * @var int|string
     */
    private $transactionKey;

    /**
     * @var BulkLoggerInterface
     */
    private $bulkLogger;

    /**
     * GenericIndexerHandler constructor.
     *
     * @param BulkLoggerInterface $bulkLogger
     * @param IndexOperationProviderInterface $indexOperationProvider
     * @param IndexerRegistry $indexerRegistry
     * @param Batch $batch
     * @param TransactionKeyInterface $transactionKey
     * @param string $indexIdentifier
     * @param string $typeName
     */
    public function __construct(
        BulkLoggerInterface $bulkLogger,
        IndexOperationProviderInterface $indexOperationProvider,
        IndexerRegistry $indexerRegistry,
        Batch $batch,
        TransactionKeyInterface $transactionKey,
        string $indexIdentifier,
        string $typeName
    ) {
        $this->bulkLogger = $bulkLogger;
        $this->batch = $batch;
        $this->indexOperationProvider = $indexOperationProvider;
        $this->typeName = $typeName;
        $this->indexIdentifier = $indexIdentifier;
        $this->indexerRegistry = $indexerRegistry;
        $this->transactionKey = $transactionKey->load();
    }

    /**
     * Update documents in ES
     *
     * @param Traversable $documents
     * @param StoreInterface $store
     * @param array $requireDataProvides
     *
     * @return $this
     */
    public function updateIndex(Traversable $documents, StoreInterface $store, array $requireDataProvides)
    {
        try {
            $index = $this->getIndex($store);
            $type = $index->getType($this->typeName);
            $dataProviders = [];

            foreach ($type->getDataProviders() as $name => $dataProvider) {
                if (in_array($name, $requireDataProvides)) {
                    $dataProviders[] = $dataProvider;
                }
            }

            if (empty($dataProviders)) {
                return $this;
            }

            $storeId = (int)$store->getId();

            foreach ($this->batch->getItems($documents, $this->getBatchSize($storeId)) as $docs) {
                /** @var DataProviderInterface $datasource */
                foreach ($dataProviders as $datasource) {
                    if (!empty($docs)) {
                        $docs = $datasource->addData($docs, $storeId);
                    }
                }

                $bulkRequest = $this->getIndexOperation($store->getId())->createBulk()->updateDocuments(
                    $index->getName(),
                    $this->typeName,
                    $docs
                );

                $response = $this->getIndexOperation($store->getId())->executeBulk($bulkRequest);
                $this->bulkLogger->log($response);
                $docs = null;
            }

            $this->getIndexOperation($store->getId())->refreshIndex($index);
        } catch (ConnectionDisabledException $exception) {
            // do nothing, ES indexer disabled in configuration
        }
    }

    /**
     * Save documents in ES
     *
     * @param Traversable $documents
     * @param StoreInterface $store
     *
     * @return void
     */
    public function saveIndex(Traversable $documents, StoreInterface $store)
    {
        try {
            $index = $this->getIndex($store);
            $type = $index->getType($this->typeName);

            $storeId = (int)$store->getId();

            foreach ($this->batch->getItems($documents, $this->getBatchSize($storeId)) as $docs) {
                foreach ($type->getDataProviders() as $dataProvider) {
                    if (!empty($docs)) {
                        $docs = $dataProvider->addData($docs, $storeId);
                    }
                }

                if (!empty($docs)) {
                    $bulkRequest = $this->getIndexOperation($store->getId())->createBulk()->addDocuments(
                        $index->getName(),
                        $this->typeName,
                        $docs
                    );

                    $response = $this->getIndexOperation($store->getId())->executeBulk($bulkRequest);
                    $this->bulkLogger->log($response);
                }

                $docs = null;
            }

            if ($index->isNew() && !$this->indexerRegistry->isFullReIndexationRunning()) {
                $this->getIndexOperation($store->getId())->switchIndexer($index->getName(), $index->getIdentifier());
            }

            $this->getIndexOperation($store->getId())->refreshIndex($index);
        } catch (ConnectionDisabledException $exception) {
            // do nothing, ES indexer disabled in configuration
        }
    }

    /**
     * Removed unnecessary documents in ES by transaction key
     *
     * @param StoreInterface $store
     * @param array $docIds
     *
     * @return void
     */
    public function cleanUpByTransactionKey(StoreInterface $store, array $docIds = null)
    {
        try {
            $indexAlias = $this->getIndexOperation($store->getId())->getIndexAlias($store);

            if ($this->getIndexOperation($store->getId())->indexExists($indexAlias)) {
                $index = $this->getIndexOperation($store->getId())->getIndexByName($this->indexIdentifier, $store);
                $transactionKeyQuery = ['must_not' => ['term' => ['tsk' => $this->transactionKey]]];
                $query = ['query' => ['bool' => $transactionKeyQuery]];

                if ($docIds) {
                    $query['query']['bool']['must']['terms'] = ['_id' => array_values($docIds)];
                }

                $query = [
                    'index' => $index->getName(),
                    'type' => $this->typeName,
                    'body' => $query,
                ];

                $this->getIndexOperation($store->getId())->deleteByQuery($query);
            }
        } catch (ConnectionDisabledException $exception) {
            // do nothing, ES indexer disabled in configuration
        }
    }

    /**
     * Get batch size
     *
     * @param int $storeId
     *
     * @return int
     */
    private function getBatchSize(int $storeId): int
    {
        return $this->getIndexOperation($storeId)->getBatchIndexingSize();
    }

    /**
     * Get Index
     *
     * @param StoreInterface $store
     *
     * @return IndexInterface
     */
    private function getIndex(StoreInterface $store)
    {
        try {
            $index = $this->getIndexOperation($store->getId())->getIndexByName($this->indexIdentifier, $store);
        } catch (Exception $e) {
            $index = $this->getIndexOperation($store->getId())->createIndex($this->indexIdentifier, $store);
        }

        return $index;
    }

    /**
     * Get Index operations
     *
     * @param int $storeId
     *
     * @return IndexOperationInterface
     */
    private function getIndexOperation(int $storeId): IndexOperationInterface
    {
        return $this->indexOperationProvider->getOperationByStore($storeId);
    }

    /**
     * Get type name
     *
     * @return string
     */
    public function getTypeName()
    {
        return $this->typeName;
    }
}
