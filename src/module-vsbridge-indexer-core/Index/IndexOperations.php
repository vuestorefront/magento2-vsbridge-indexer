<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Index;

use Divante\VsbridgeIndexerCore\Api\Client\ClientInterface;
use Divante\VsbridgeIndexerCore\Api\BulkResponseInterfaceFactory as BulkResponseFactory;
use Divante\VsbridgeIndexerCore\Api\BulkRequestInterface;
use Divante\VsbridgeIndexerCore\Api\BulkRequestInterfaceFactory as BulkRequestFactory;
use Divante\VsbridgeIndexerCore\Api\IndexInterface;
use Divante\VsbridgeIndexerCore\Api\IndexInterfaceFactory as IndexFactory;
use Divante\VsbridgeIndexerCore\Api\IndexOperationInterface;
use Divante\VsbridgeIndexerCore\Api\Index\TypeInterface;
use Divante\VsbridgeIndexerCore\Api\MappingInterface;
use Divante\VsbridgeIndexerCore\Elasticsearch\ClientResolver;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class IndexOperations
 */
class IndexOperations implements IndexOperationInterface
{
    /**
     * @var ClientResolver
     */
    private $clientResolver;

    /**
     * @var IndexFactory
     */
    private $indexFactory;

    /**
     * @var BulkResponseFactory
     */
    private $bulkResponseFactory;

    /**
     * @var BulkRequestFactory
     */
    private $bulkRequestFactory;

    /**
     * @var IndexSettings
     */
    private $indexSettings;

    /**
     * @var array
     */
    private $indicesConfiguration;

    /**
     * @var array
     */
    private $indicesByIdentifier;

    /**
     * IndexOperations constructor.
     *
     * @param ClientResolver $clientResolver
     * @param BulkResponseFactory $bulkResponseFactory
     * @param BulkRequestFactory $bulkRequestFactory
     * @param IndexSettings $indexSettings
     * @param IndexFactory $indexFactory
     */
    public function __construct(
        ClientResolver $clientResolver,
        BulkResponseFactory $bulkResponseFactory,
        BulkRequestFactory $bulkRequestFactory,
        IndexSettings $indexSettings,
        IndexFactory $indexFactory
    ) {
        $this->clientResolver = $clientResolver;
        $this->indexFactory = $indexFactory;
        $this->indexSettings = $indexSettings;
        $this->bulkResponseFactory = $bulkResponseFactory;
        $this->bulkRequestFactory = $bulkRequestFactory;
    }

    /**
     * @inheritdoc
     */
    public function executeBulk($storeId, BulkRequestInterface $bulk)
    {
        if ($bulk->isEmpty()) {
            throw new \LogicException('Can not execute empty bulk.');
        }

        $bulkParams = ['body' => $bulk->getOperations()];
        $rawBulkResponse = $this->resolveClient($storeId)->bulk($bulkParams);

        return $this->bulkResponseFactory->create(
            ['rawResponse' => $rawBulkResponse]
        );
    }

    /**
     * @inheritdoc
     */
    public function deleteByQuery($storeId, array $params)
    {
        $this->resolveClient($storeId)->deleteByQuery($params);
    }

    /**
     * @inheritdoc
     */
    public function indexExists($storeId, $indexName)
    {
        $exists = true;

        if (!isset($this->indicesByIdentifier[$indexName])) {
            $exists = $this->resolveClient($storeId)->indexExists($indexName);
        }

        return $exists;
    }

    /**
     * @inheritdoc
     */
    public function getIndexByName($indexIdentifier, StoreInterface $store)
    {
        $indexAlias = $this->getIndexAlias($store);

        if (!isset($this->indicesByIdentifier[$indexAlias])) {
            if (!$this->indexExists($store->getId(), $indexAlias)) {
                throw new \LogicException(
                    "{$indexIdentifier} index does not exist yet."
                );
            }

            $this->initIndex($indexIdentifier, $store, true);
        }

        return $this->indicesByIdentifier[$indexAlias];
    }

    /**
     * @inheritdoc
     */
    public function getIndexAlias(StoreInterface $store)
    {
        return $this->indexSettings->getIndexAlias($store);
    }

    /**
     * @inheritdoc
     */
    public function createIndex($indexIdentifier, StoreInterface $store)
    {
        $index = $this->initIndex($indexIdentifier, $store, false);

        $this->resolveClient($store->getId())->createIndex(
            $index->getName(),
            $this->indexSettings->getEsConfig()
        );

        /** @var TypeInterface $type */
        foreach ($index->getTypes() as $type) {
            $mapping = $type->getMapping();

            if ($mapping instanceof MappingInterface) {
                $this->resolveClient($store->getId())->putMapping(
                    $index->getName(),
                    $type->getName(),
                    $mapping->getMappingProperties()
                );
            }
        }

        return $index;
    }

    /**
     * @inheritdoc
     */
    public function refreshIndex($storeId, IndexInterface $index)
    {
        $this->resolveClient($storeId)->refreshIndex($index->getName());
    }

    /**
     * @inheritdoc
     */
    public function switchIndexer($storeId, string $indexName, string $indexAlias)
    {
        $aliasActions = [
            [
                'add' => [
                    'index' => $indexName,
                    'alias' => $indexAlias,
                ]
            ]
        ];

        $deletedIndices = [];
        $oldIndices = $this->resolveClient($storeId)->getIndicesNameByAlias($indexAlias);

        foreach ($oldIndices as $oldIndexName) {
            if ($oldIndexName != $indexName) {
                $deletedIndices[] = $oldIndexName;
                $aliasActions[]   = [
                    'remove' => [
                        'index' => $oldIndexName,
                        'alias' => $indexAlias,
                    ]
                ];
            }
        }

        $this->resolveClient($storeId)->updateAliases($aliasActions);

        foreach ($deletedIndices as $deletedIndex) {
            $this->resolveClient($storeId)->deleteIndex($deletedIndex);
        }
    }

    /**
     * @param $indexIdentifier
     * @param StoreInterface $store
     * @param bool $existingIndex
     *
     * @return Index
     */
    private function initIndex($indexIdentifier, StoreInterface $store, $existingIndex)
    {
        $this->getIndicesConfiguration();

        if (!isset($this->indicesConfiguration[$indexIdentifier])) {
            throw new \LogicException('No configuration found');
        }

        $indexAlias = $this->getIndexAlias($store);
        $indexName = $this->indexSettings->createIndexName($store);

        if ($existingIndex) {
            $indexName = $indexAlias;
        }

        $config = $this->indicesConfiguration[$indexIdentifier];

        /** @var Index $index */
        $index = $this->indexFactory->create(
            [
                'name' => $indexName,
                'alias' => $indexAlias,
                'types' => $config['types'],
            ]
        );

        return $this->indicesByIdentifier[$indexAlias] = $index;
    }

    /**
     * @return BulkRequestInterface
     */
    public function createBulk()
    {
        return $this->bulkRequestFactory->create();
    }

    /**
     * @return int
     */
    public function getBatchIndexingSize()
    {
        return $this->indexSettings->getBatchIndexingSize();
    }

    /**
     * @return array
     */
    private function getIndicesConfiguration()
    {
        if (null === $this->indicesConfiguration) {
            $this->indicesConfiguration = $this->indexSettings->getIndicesConfig();
        }

        return $this->indicesConfiguration;
    }

    /**
     * @param int $storeId
     *
     * @return ClientInterface
     */
    private function resolveClient($storeId): ClientInterface
    {
        return $this->clientResolver->getClient($storeId);
    }
}
