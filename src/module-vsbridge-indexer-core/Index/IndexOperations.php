<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Index;

use Divante\VsbridgeIndexerCore\Api\Client\ClientInterface;
use Divante\VsbridgeIndexerCore\Api\BulkResponseInterface;
use Divante\VsbridgeIndexerCore\Api\BulkResponseInterfaceFactory as BulkResponseFactory;
use Divante\VsbridgeIndexerCore\Api\BulkRequestInterface;
use Divante\VsbridgeIndexerCore\Api\BulkRequestInterfaceFactory as BulkRequestFactory;
use Divante\VsbridgeIndexerCore\Api\IndexInterface;
use Divante\VsbridgeIndexerCore\Api\IndexInterfaceFactory as IndexFactory;
use Divante\VsbridgeIndexerCore\Api\IndexOperationInterface;
use Divante\VsbridgeIndexerCore\Api\Index\TypeInterface;
use Divante\VsbridgeIndexerCore\Api\MappingInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class IndexOperations
 */
class IndexOperations implements IndexOperationInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

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
     * @param ClientInterface $client
     * @param BulkResponseFactory $bulkResponseFactory
     * @param BulkRequestFactory $bulkRequestFactory
     * @param IndexSettings $indexSettings
     * @param IndexFactory $indexFactory
     */
    public function __construct(
        ClientInterface $client,
        BulkResponseFactory $bulkResponseFactory,
        BulkRequestFactory $bulkRequestFactory,
        IndexSettings $indexSettings,
        IndexFactory $indexFactory
    ) {
        $this->client = $client;
        $this->indexFactory = $indexFactory;
        $this->indexSettings = $indexSettings;
        $this->bulkResponseFactory = $bulkResponseFactory;
        $this->bulkRequestFactory = $bulkRequestFactory;
    }

    /**
     * @inheritdoc
     */
    public function executeBulk(BulkRequestInterface $bulk)
    {
        if ($bulk->isEmpty()) {
            throw new \LogicException('Can not execute empty bulk.');
        }

        $bulkParams = ['body' => $bulk->getOperations()];
        $rawBulkResponse = $this->client->bulk($bulkParams);

        return $this->bulkResponseFactory->create(
            ['rawResponse' => $rawBulkResponse]
        );
    }

    /**
     * @inheritdoc
     */
    public function deleteByQuery(array $params)
    {
        $this->client->deleteByQuery($params);
    }

    /**
     * @inheritdoc
     */
    public function indexExists($indexName)
    {
        $exists = true;

        if (!isset($this->indicesByIdentifier[$indexName])) {
            $exists = $this->client->indexExists($indexName);
        }

        return $exists;
    }

    /**
     * @inheritdoc
     */
    public function getIndexByName($indexIdentifier, StoreInterface $store)
    {
        $indexAlias = $this->getIndexAlias($indexIdentifier, $store);

        if (!isset($this->indicesByIdentifier[$indexAlias])) {
            if (!$this->indexExists($indexAlias)) {
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
    public function getIndexAlias($indexIdentifier, StoreInterface $store)
    {
        return $this->indexSettings->getIndexAlias($store, $indexIdentifier);
    }

    /**
     * @inheritdoc
     */
    public function createIndex($indexIdentifier, StoreInterface $store)
    {
        $index = $this->initIndex($indexIdentifier, $store, false);
        $this->client->createIndex(
            $index->getName(),
            $this->indexSettings->getEsConfig()
        );

        $mapping = $index->getMapping();

        if ($mapping instanceof MappingInterface) {
            $this->client->putMapping(
                $index->getName(),
                $index->getType(),
                $mapping->getMappingProperties()
            );
        }

        return $index;
    }

    /**
     * @inheritdoc
     */
    public function refreshIndex(IndexInterface $index)
    {
        $this->client->refreshIndex($index->getName());
    }

    /**
     * @inheritdoc
     */
    public function switchIndexer(string $indexName, string $indexAlias)
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
        $oldIndices = $this->client->getIndicesNameByAlias($indexAlias);

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

        $this->client->updateAliases($aliasActions);

        foreach ($deletedIndices as $deletedIndex) {
            $this->client->deleteIndex($deletedIndex);
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

        $indexName = $this->indexSettings->createIndexName($store, $indexIdentifier);
        $indexAlias = $this->indexSettings->getIndexAlias($store, $indexIdentifier);

        if ($existingIndex) {
            $indexName = $indexAlias;
        }

        $config = $this->indicesConfiguration[$indexIdentifier];

        $index = $this->indexFactory->create(
            [
                'name' => $indexName,
                'newIndex' => !$existingIndex,
                'identifier' => $indexAlias,
                'dataProviders' => $config['dataProviders'],
                'mapping' => $config['mapping'],
            ]
        );

        $this->indicesByIdentifier[$indexAlias] = $index;

        return $this->indicesByIdentifier[$indexAlias];
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
}
