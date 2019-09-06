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
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

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
        LoggerInterface $logger,
        IndexFactory $indexFactory
    ) {
        $this->logger = $logger;
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
        $storeIdentifier = ('code' === $this->indexSettings->getIndexIdentifier())
            ? $store->getCode()
            : $store->getId();
        $name = $this->indexSettings->getIndexNamePrefix() . '_' . $storeIdentifier;
        if ($this->getUseVersioning($store)) {
            $name = $this->getNewIndexVersionName($name);
        }
        return $name;
    }

    /**
     * @inheritdoc
     */
    public function getIndexBaseName(StoreInterface $store)
    {
        $storeIdentifier = ('code' === $this->indexSettings->getIndexIdentifier())
            ? $store->getCode()
            : $store->getId();
        $baseName = $this->indexSettings->getIndexNamePrefix() . '_' . $storeIdentifier;
        return $baseName;
    }


    /**
     * @inheritdoc
     */
    public function getUseVersioning(StoreInterface $store)
    {
        $useVersioning = $this->indexSettings->getUseVersioning();
        return $useVersioning ? $useVersioning : false ;
    }

    /**
     * @inheritdoc
     */
    public function createIndex($indexIdentifier, StoreInterface $store)
    {
        $index = $this->initIndex($indexIdentifier, $store);

        if ($this->client->indexExists($index->getName())) {
            $indexCorrect = true;
            try {
                //try to get settings and check if mappings are in the index to be sure index was created properly before

                $params['index'] = $index->getName();
                $settings = $this->client->getSettings($params);
                $mapping = $this->client->getMapping($params);
                foreach ($index->getTypes() as $type) {
                    if (!isset($mapping[$index->getName()]['mappings']) ||
                        !in_array($type->getName(), array_keys($mapping[$index->getName()]['mappings']))
                    ){
                        $indexCorrect = false;
                    }
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
            if (!$indexCorrect) {
                //delete incorrect index
                $this->deleteIndexByName($index->getName());
            } else {
                return $index;
            }
        }

        try {
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
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return false;
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
    public function deleteIndexByName($indexIdentifier)
    {
        if ($this->client->indexExists($indexIdentifier)) {
            $this->client->deleteIndex($indexIdentifier);
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


    /**
     * Get an index name with a next version number. We need this for zero downtime reindex
     *
     * @param        $storeId
     * @param string $scope
     * @return string
     */
    public function getNewIndexVersionName($indexBaseName)
    {
        $existingIndexes = $this->client->getAliases(
            array('index' => $this->getIndexWithVersionName($indexBaseName) . '*')
        );

        if (empty($existingIndexes)) {
            // no indexes yet, return version v1 name
            return $this->getIndexWithVersionName($indexBaseName, '1');
        }

        // Find a current max index version
        // (should be one normally, but we loop if multiple indexes returned by some reason)
        $maxVersion = 0;
        foreach ($existingIndexes as $indexName => $indexInfo) {
            if (!isset($indexInfo['aliases']) || empty($indexInfo['aliases'])) {
                continue;
            }

            $indexVersion = intval(str_replace($this->getIndexWithVersionName($indexBaseName), '', $indexName));
            if ($indexVersion > $maxVersion) {
                $maxVersion = $indexVersion;
            }
        }

        return $this->getIndexWithVersionName($indexBaseName, ++$maxVersion);
    }


    /**
     * Form a name for an index with version number
     *
     * @param        $indexBaseName
     * @param string $version
     * @return string
     */
    public function getIndexWithVersionName($indexBaseName, $version = '')
    {
        return $indexBaseName . "_v" . $version;
    }

    /**
     * @param        $indexBaseName
     * @param        $newIndexName
     * @param string $scope
     */
    /**
     * @param        $storeId
     * @param        $newIndexName
     */
    public function deleteOldIndexesAndRealiasToNew(StoreInterface $store, $newIndexName)
    {
        $oldFormatIndex = array();
        $indexBaseName  = $this->getIndexBaseName($store);

        /*
         * For backward compatibility, when we didn't have index versions and aliases.
         * Make sure that old format index deleted
         */
        if ($this->client->indexExists($indexBaseName)) {
            try {
                $oldFormatIndex = $this->client->getAliases(
                    array('index' => $indexBaseName)
                );
            } catch (Elasticsearch\Common\Exceptions\Missing404Exception $e) {
                // there is no old format indices
            }
        }

        $oldFormatIndexKeys = array_keys($oldFormatIndex);

        if (!empty($oldFormatIndex) && reset($oldFormatIndexKeys) == $indexBaseName) {
            $this->deleteIndexByName($indexBaseName);
        }

        $existingIndexes = $this->client->getAliases(
            array('index' => $this->getIndexWithVersionName($indexBaseName) . '*')
        );

        if (empty($existingIndexes)) {
            return;
        }

        $indexesToDelete = array();
        $indexesActions  = array();
        foreach ($existingIndexes as $indexName => $indexInfo) {
            if (!isset($indexInfo['aliases']) || empty($indexInfo['aliases'])) {
                continue;
            }

            if ($newIndexName == $indexName) {
                continue;
            }

            foreach ($indexInfo['aliases'] as $alias => $aliasInfo) {
                $indexesActions[] = array(
                    'remove' => array(
                        'index' => $indexName,
                        'alias' => $alias,
                    ),
                );
            }

            $indexesToDelete[] = $indexName;
        }

        $indexesActions[] = array(
            'add' => array(
                'index' => $newIndexName,
                'alias' => $indexBaseName,
            ),
        );

        try {
            // unassign alias from old reindex and assign it to a new one
            $this->client->updateAliases(
                array(
                    'body' => array(
                        'actions' => $indexesActions,
                    ),
                )
            );

            // finally delete old indexes
            foreach ($indexesToDelete as $indexToDelete) {
                $this->deleteIndexByName($indexToDelete);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
