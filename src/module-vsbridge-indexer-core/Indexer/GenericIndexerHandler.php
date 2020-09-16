<?php

namespace Divante\VsbridgeIndexerCore\Indexer;

use Divante\VsbridgeIndexerCore\Api\BulkLoggerInterface;
use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeIndexerCore\Api\IndexInterface;
use Divante\VsbridgeIndexerCore\Api\Index\TransactionKeyInterface;
use Divante\VsbridgeIndexerCore\Api\IndexOperationInterface;
use Divante\VsbridgeIndexerCore\Exception\ConnectionDisabledException;
use Divante\VsbridgeIndexerCore\Exception\ConnectionUnhealthyException;
use Divante\VsbridgeIndexerCore\Logger\IndexerLogger;
use Divante\VsbridgeIndexerCore\Index\IndexSettings;
use Divante\VsbridgeIndexerCore\Model\IndexerRegistry;
use Exception;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Store\Api\Data\StoreInterface;
use Traversable;
use Divante\VsbridgeIndexerCore\Api\Index\DataProviderResolverInterface;
use Divante\VsbridgeIndexerCore\Model\ElasticsearchResolverInterface;

/**
 * Class IndexerHandler
 *
 * TODO refactor - coupling between objects
 */
class GenericIndexerHandler
{
    /**
     * @var ElasticsearchResolverInterface
     */
    private $esVersionResolver;

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
     * @var DataProviderResolverInterface
     */
    private $dataProviderResolver;

    /**
     * GenericIndexerHandler constructor.
     * @param DataProviderResolverInterface $dataProviderResolver
     * @param ElasticsearchResolverInterface $esVersionResolver
     * @param BulkLoggerInterface $bulkLogger
     * @param IndexOperationInterface $indexOperationProvider
     * @param IndexerLogger $indexerLogger
     * @param IndexOperationInterface $indexOperations
     * @param IndexerRegistry $indexerRegistry
     * @param Batch $batch
     * @param TransactionKeyInterface $transactionKey
     * @param string $typeName
     */
    public function __construct(
        DataProviderResolverInterface $dataProviderResolver,
        ElasticsearchResolverInterface $esVersionResolver,
        BulkLoggerInterface $bulkLogger,
        IndexOperationInterface $indexOperationProvider,
        IndexerLogger $indexerLogger,
        IndexOperationInterface $indexOperations,
        IndexerRegistry $indexerRegistry,
        Batch $batch,
        TransactionKeyInterface $transactionKey,
        string $typeName
    ) {
        $this->esVersionResolver = $esVersionResolver;
        $this->dataProviderResolver = $dataProviderResolver;
        $this->bulkLogger = $bulkLogger;
        $this->batch = $batch;
        $this->indexOperations = $indexOperations;
        $this->typeName = $typeName;
        $this->indexerLogger = $indexerLogger;
        $this->indexerRegistry = $indexerRegistry;
        $this->transactionKey = $transactionKey;
    }

    /**
     * Partial document update in ES
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

            foreach ($this->getDataProviders() as $name => $dataProvider) {
                if (in_array($name, $requireDataProvides)) {
                    $dataProviders[] = $dataProvider;
                }
            }

            if (empty($dataProviders)) {
                return $this;
            }

            $batchSize = $this->indexOperations->getBatchIndexingSize();

            foreach ($this->batch->getItems($documents, $batchSize) as $docs) {
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
            $storeId = (int)$store->getId();
            $batchSize = $this->indexOperations->getBatchIndexingSize();

            foreach ($this->batch->getItems($documents, $batchSize) as $docs) {
                foreach ($this->getDataProviders() as $dataProvider) {
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
            $indexAlias = $this->indexOperations->getIndexAlias($this->getIdentifier(), $store);

            if ($this->indexOperations->indexExists($store->getId(), $indexAlias)) {
                $index = $this->indexOperations->getIndexByName($this->getIdentifier(), $store);
                $transactionKeyQuery = ['must_not' => ['term' => ['tsk' => $this->transactionKey->load()]]];
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
     * @return DataProviderInterface[]
     */
    private function getDataProviders()
    {
        return $this->dataProviderResolver->getDataProviders($this->typeName);
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
            $index = $this->indexOperations->getIndexByName($this->getIdentifier(), $store);
        } catch (Exception $e) {
            $index = $this->indexOperations->createIndex($this->getIdentifier(), $store);
        }

        return $index;
    }

    /**
     * @param StoreInterface $store
     */
    public function createIndex(StoreInterface $store)
    {
        $this->indexOperations->createIndex($this->getIdentifier(), $store);
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

    /**
     * @return string
     */
    private function getIdentifier(): string
    {
        $esVersion = $this->esVersionResolver->getVersion();

        if ($esVersion === ElasticsearchResolverInterface::DEFAULT_ES_VERSION) {
            return IndexSettings::DUMMY_INDEX_IDENTIFIER;
        }

        return $this->typeName;
    }
}
