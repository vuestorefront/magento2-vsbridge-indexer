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
use Divante\VsbridgeIndexerCore\Api\Indexer\TransactionKeyInterface;
use Divante\VsbridgeIndexerCore\Api\IndexOperationInterface;
use Divante\VsbridgeIndexerCore\Exception\ConnectionDisabledException;
use Divante\VsbridgeIndexerCore\Exception\ConnectionUnhealthyException;
use Divante\VsbridgeIndexerCore\Logger\IndexerLogger;
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
     * @var IndexOperationInterface
     */
    private $indexOperations;

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
     * @var IndexerLogger
     */
    private $indexerLogger;

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
     * @param IndexOperationInterface $indexOperationProvider
     * @param IndexerLogger $indexerLogger
     * @param IndexerRegistry $indexerRegistry
     * @param Batch $batch
     * @param TransactionKeyInterface $transactionKey
     * @param string $indexIdentifier
     * @param string $typeName
     */
    public function __construct(
        BulkLoggerInterface $bulkLogger,
        IndexOperationInterface $indexOperationProvider,
        IndexerLogger $indexerLogger,
        IndexerRegistry $indexerRegistry,
        Batch $batch,
        TransactionKeyInterface $transactionKey,
        string $indexIdentifier,
        string $typeName
    ) {
        $this->bulkLogger = $bulkLogger;
        $this->batch = $batch;
        $this->indexOperations = $indexOperationProvider;
        $this->typeName = $typeName;
        $this->indexIdentifier = $indexIdentifier;
        $this->indexerLogger = $indexerLogger;
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
     * @throws ConnectionUnhealthyException
     */
    public function updateIndex(Traversable $documents, StoreInterface $store, array $requireDataProvides)
    {
        try {
            $index = $this->getIndex($store);
            $type = $index->getType($this->typeName);
            $storeId = (int)$store->getId();
            $dataProviders = [];

            foreach ($type->getDataProviders() as $name => $dataProvider) {
                if (in_array($name, $requireDataProvides)) {
                    $dataProviders[] = $dataProvider;
                }
            }

            if (empty($dataProviders)) {
                return $this;
            }

            $batchSize = $this->indexOperations->getBatchIndexingSize();

            foreach ($this->batch->getItems($documents, $batchSize) as $docs) {
                /** @var DataProviderInterface $datasource */
                foreach ($dataProviders as $datasource) {
                    if (!empty($docs)) {
                        $docs = $datasource->addData($docs, $storeId);
                    }
                }

                $bulkRequest = $this->indexOperations->createBulk()->updateDocuments(
                    $index->getName(),
                    $this->typeName,
                    $docs
                );

                $this->indexOperations->optimizeEsIndexing($storeId, $index->getName());
                $response = $this->indexOperations->executeBulk($storeId, $bulkRequest);
                $this->indexOperations->cleanAfterOptimizeEsIndexing($storeId, $index->getName());
                $this->bulkLogger->log($response);
                $docs = null;
            }

            $this->indexOperations->refreshIndex($store->getId(), $index);
        } catch (ConnectionDisabledException $exception) {
            // do nothing, ES indexer disabled in configuration
        } catch (ConnectionUnhealthyException $exception) {
            $this->indexerLogger->error($exception->getMessage());
            $this->indexOperations->cleanAfterOptimizeEsIndexing($storeId, $index->getName());
            throw $exception;
        }
    }

    /**
     * Save documents in ES
     *
     * @param Traversable $documents
     * @param StoreInterface $store
     *
     * @return void
     * @throws ConnectionUnhealthyException
     */
    public function saveIndex(Traversable $documents, StoreInterface $store)
    {
        try {
            $index = $this->getIndex($store);
            $type = $index->getType($this->typeName);
            $storeId = (int)$store->getId();
            $batchSize = $this->indexOperations->getBatchIndexingSize();

            foreach ($this->batch->getItems($documents, $batchSize) as $docs) {
                foreach ($type->getDataProviders() as $dataProvider) {
                    if (!empty($docs)) {
                        $docs = $dataProvider->addData($docs, $storeId);
                    }
                }

                if (!empty($docs)) {
                    $bulkRequest = $this->indexOperations->createBulk()->addDocuments(
                        $index->getName(),
                        $this->typeName,
                        $docs
                    );

                    $this->indexOperations->optimizeEsIndexing($storeId, $index->getName());
                    $response = $this->indexOperations->executeBulk($storeId, $bulkRequest);
                    $this->indexOperations->cleanAfterOptimizeEsIndexing($storeId, $index->getName());
                    $this->bulkLogger->log($response);
                }

                $docs = null;
            }

            if ($index->isNew() && !$this->indexerRegistry->isFullReIndexationRunning()) {
                $this->indexOperations->switchIndexer($store->getId(), $index->getName(), $index->getAlias());
            }

            $this->indexOperations->refreshIndex($store->getId(), $index);
        } catch (ConnectionDisabledException $exception) {
            // do nothing, ES indexer disabled in configuration
        } catch (ConnectionUnhealthyException $exception) {
            $this->indexerLogger->error($exception->getMessage());
            $this->indexOperations->cleanAfterOptimizeEsIndexing($storeId, $index->getName());
            throw $exception;
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
            $indexAlias = $this->indexOperations->getIndexAlias($store);

            if ($this->indexOperations->indexExists($store->getId(), $indexAlias)) {
                $index = $this->indexOperations->getIndexByName($this->indexIdentifier, $store);
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

                $this->indexOperations->deleteByQuery($store->getId(), $query);
            }
        } catch (ConnectionDisabledException $exception) {
            // do nothing, ES indexer disabled in configuration
        }
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
            $index = $this->indexOperations->getIndexByName($this->indexIdentifier, $store);
        } catch (Exception $e) {
            $index = $this->indexOperations->createIndex($this->indexIdentifier, $store);
        }

        return $index;
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
