<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Indexer;

use Divante\VsbridgeIndexerCore\Api\BulkResponseInterface;
use Divante\VsbridgeIndexerCore\Api\Client\ClientInterface;
use Divante\VsbridgeIndexerCore\Api\ConvertDataTypesInterface;
use Divante\VsbridgeIndexerCore\Api\IndexInterface;
use Divante\VsbridgeIndexerCore\Api\IndexOperationInterface;
use Divante\VsbridgeIndexerCore\Api\Indexer\TransactionKeyInterface;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Store\Api\Data\StoreInterface;
use Psr\Log\LoggerInterface;

/**
 * Class IndexerHandler
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
    private $indexOperation;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $typeName;

    /**
     * @var string
     */
    private $indexIdentifier;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var ConvertDataTypesInterface
     */
    private $convertDataTypes;

    /**
     * @var int|string
     */
    private $transactionKey;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * IndexerHandler constructor.
     *
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     * @param IndexOperationInterface $indexOperation
     * @param ConvertDataTypesInterface $convertDataTypes
     * @param EventManager $eventManager
     * @param Batch $batch
     * @param TransactionKeyInterface $transactionKey
     * @param string $indexIdentifier
     * @param string $typeName
     */
    public function __construct(
        ClientInterface $client,
        LoggerInterface $logger,
        IndexOperationInterface $indexOperation,
        ConvertDataTypesInterface $convertDataTypes,
        EventManager $eventManager,
        Batch $batch,
        TransactionKeyInterface $transactionKey,
        string $indexIdentifier,
        string $typeName
    ) {
        $this->logger = $logger;
        $this->batch = $batch;
        $this->client = $client;
        $this->indexOperation = $indexOperation;
        $this->convertDataTypes = $convertDataTypes;
        $this->typeName = $typeName;
        $this->indexIdentifier = $indexIdentifier;
        $this->eventManager = $eventManager;
        $this->transactionKey = $transactionKey->load();
    }

    /**
     * @param \Traversable $documents
     * @param StoreInterface $store
     *
     * @return void
     */
    public function saveIndex(\Traversable $documents, StoreInterface $store)
    {
        $index = $this->getIndex($store);
        $type = $index->getType($this->typeName);

        foreach ($this->batch->getItems($documents, $this->getBatchSize()) as $docs) {
            foreach ($type->getDataProviders() as $dataProvider) {
                if (!empty($docs)) {
                    $docs = $dataProvider->addData($docs, (int)$store->getId());
                }
            }

            $docs = $this->convertDataTypes->castFieldsUsingMapping($type, $docs);
            $bulkRequest = $this->indexOperation->createBulk()->addDocuments(
                $index->getName(),
                $this->typeName,
                $docs
            );

            $response = $this->indexOperation->executeBulk($bulkRequest);
            $this->logErrors($response);
            $this->eventManager->dispatch(
                'search_engine_save_documents_after',
                [
                    'data_type' => $this->typeName,
                    'bulk_response' => $response,
                ]
            );

            $docs = null;
        }

        $this->indexOperation->refreshIndex($index);
    }

    /**
     * @param StoreInterface $store
     * @param array $docIds
     *
     * @return void
     */
    public function cleanUpByTransactionKey(StoreInterface $store, array $docIds = null)
    {
        $indexName = $this->indexOperation->getIndexName($store);

        if ($this->indexOperation->indexExists($indexName)) {
            $index = $this->indexOperation->getIndexByName($this->indexIdentifier, $store);
            $transactionKeyQuery = ['must_not' => ['term' => ['tsk' => $this->transactionKey]]];
            $query = ['query' => ['bool' => $transactionKeyQuery]];

            if ($docIds) {
                $query['query']['bool']['must']['terms'] = ['_id' => $docIds];
            }

            $query = [
                'index' => $index->getName(),
                'type' => $this->typeName,
                'body' => $query,
            ];

            $this->indexOperation->deleteByQuery($query);
        }
    }

    /**
     * @return int
     */
    private function getBatchSize()
    {
        return $this->indexOperation->getBatchIndexingSize();
    }

    /**
     * @param StoreInterface $store
     *
     * @return IndexInterface
     */
    private function getIndex(StoreInterface $store)
    {
        try {
            $index = $this->indexOperation->getIndexByName($this->indexIdentifier, $store);
        } catch (\Exception $e) {
            $index = $this->indexOperation->createIndex($this->indexIdentifier, $store);
        }

        return $index;
    }

    /**
     * @param BulkResponseInterface $bulkResponse
     */
    private function logErrors(BulkResponseInterface $bulkResponse)
    {
        if ($bulkResponse->hasErrors()) {
            $aggregateErrorsByReason = $bulkResponse->aggregateErrorsByReason();

            foreach ($aggregateErrorsByReason as $error) {
                $docIds = implode(', ', array_slice($error['document_ids'], 0, 10));
                $errorMessages = [
                    sprintf(
                        "Bulk %s operation failed %d times in index %s for type %s.",
                        $error['operation'],
                        $error['count'],
                        $error['index'],
                        $error['document_type']
                    ),
                    sprintf(
                        "Error (%s) : %s.",
                        $error['error']['type'],
                        $error['error']['reason']
                    ),
                    sprintf(
                        "Failed doc ids sample : %s.",
                        $docIds
                    ),
                ];

                $this->logger->error(implode(" ", $errorMessages));
                $errorMessages = null;
            }
        }
    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        return $this->typeName;
    }
}
