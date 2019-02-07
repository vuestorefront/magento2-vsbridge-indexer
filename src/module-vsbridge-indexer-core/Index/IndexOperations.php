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
    private $indicesByName;

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

        /** @var BulkResponseInterface $bulkResponse */
        $bulkResponse = $this->bulkResponseFactory->create(
            ['rawResponse' => $rawBulkResponse]
        );

        return $bulkResponse;
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

        if (!isset($this->indicesByName[$indexName])) {
            $exists = $this->client->indexExists($indexName);
        }

        return $exists;
    }

    /**
     * @inheritdoc
     */
    public function getIndexByName($indexIdentifier, StoreInterface $store)
    {
        $indexName = $this->getIndexName($store);

        if (!isset($this->indicesByName[$indexName])) {
            if (!$this->indexExists($indexName)) {
                throw new \LogicException(
                    "{$indexIdentifier} index does not exist yet."
                );
            }

            $this->initIndex($indexIdentifier, $store);
        }

        return $this->indicesByName[$indexName];
    }

    /**
     * @inheritdoc
     */
    public function getIndexName(StoreInterface $store)
    {
        $name = $this->indexSettings->getIndexNamePrefix();

        return $name . '_' . $store->getId();
    }

    /**
     * @inheritdoc
     */
    public function createIndex($indexIdentifier, StoreInterface $store)
    {
        $index = $this->initIndex($indexIdentifier, $store);
        $this->client->createIndex(
            $index->getName(),
            $this->indexSettings->getEsConfig()
        );

        /** @var TypeInterface $type */
        foreach ($index->getTypes() as $type) {
            $mapping = $type->getMapping();

            if ($mapping instanceof MappingInterface) {
                $this->client->putMapping(
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
    public function deleteIndex($indexIdentifier, StoreInterface $store)
    {
        $index = $this->initIndex($indexIdentifier, $store);

        if ($this->client->indexExists($index->getName())) {
            $this->client->deleteIndex($index->getName());
        }
    }

    /**
     * @inheritdoc
     */
    public function refreshIndex(IndexInterface $index)
    {
        $this->client->refreshIndex($index->getName());
    }

    /**
     * @param $indexIdentifier
     * @param StoreInterface $store
     *
     * @return mixed
     */
    private function initIndex($indexIdentifier, StoreInterface $store)
    {
        $this->getIndicesConfiguration();

        if (!isset($this->indicesConfiguration[$indexIdentifier])) {
            throw new \LogicException('No configuration found');
        }

        $indexName = $this->getIndexName($store);
        $config = $this->indicesConfiguration[$indexIdentifier];
        $types = $config['types'];

        $index = $this->indexFactory->create(
            [
                'name' => $indexName,
                'types' => $types,
            ]
        );

        $this->indicesByName[$indexName] = $index;

        return $this->indicesByName[$indexName];
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
