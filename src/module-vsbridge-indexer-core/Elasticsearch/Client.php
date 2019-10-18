<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Elasticsearch;

use Divante\VsbridgeIndexerCore\Api\Client\BuilderInterface as ClientBuilder;
use Divante\VsbridgeIndexerCore\Api\Client\ConfigurationInterface as ClientConfiguration;
use Divante\VsbridgeIndexerCore\Api\Client\ClientInterface;
use Divante\VsbridgeIndexerCore\Config\GeneralSettings;
use Divante\VsbridgeIndexerCore\Exception\ConnectionDisabledException;

/**
 * Class Client
 */
class Client implements ClientInterface
{
    /**
     * @var \Divante\VsbridgeIndexerCore\Config\GeneralSettings
     */
    protected $config;

    /**
     * @var \Elasticsearch\Client
     */
    private $client;

    /**
     * @var ClientBuilder
     */
    private $clientBuilder;

    /**
     * @var ClientConfiguration
     */
    private $clientConfiguration;

    /**
     * Client constructor.
     *
     * @param ClientBuilder       $clientBuilder
     * @param ClientConfiguration $clientConfiguration
     * @param GeneralSettings     $config
     */
    public function __construct(
        ClientBuilder $clientBuilder,
        ClientConfiguration $clientConfiguration,
        GeneralSettings $config
    ) {
        $this->clientBuilder       = $clientBuilder;
        $this->clientConfiguration = $clientConfiguration;
        $this->config              = $config;
    }

    /**
     * @inheritdoc
     */
    public function bulk(array $bulkParams)
    {
        return $this->getClient()->bulk($bulkParams);
    }

    /**
     * @inheritdoc
     */
    public function createIndex($indexName, array $indexSettings)
    {
        $this->getClient()->indices()->create(
            [
                'index' => $indexName,
                'body'  => $indexSettings,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getIndicesNameByAlias(string $indexAlias): array
    {
        $indices = [];

        try {
            $indices = $this->getClient()->indices()->getMapping(['index' => $indexAlias]);
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
        }

        return array_keys($indices);
    }
    /**
     * @inheritdoc
     */
    public function updateAliases(array $aliasActions)
    {
        $this->getClient()->indices()->updateAliases(['body' => ['actions' => $aliasActions]]);
    }

    /**
     * @inheritdoc
     */
    public function refreshIndex($indexName)
    {
        $this->getClient()->indices()->refresh(['index' => $indexName]);
    }

    /**
     * @inheritdoc
     */
    public function indexExists($indexName)
    {
        return $this->getClient()->indices()->exists(['index' => $indexName]);
    }

    /**
     * @inheritdoc
     */
    public function deleteIndex($indexName)
    {
        return $this->getClient()->indices()->delete(['index' => $indexName]);
    }

    /**
     * @inheritdoc
     */
    public function putMapping($indexName, $type, array $mapping)
    {
        $this->getClient()->indices()->putMapping(
            [
                'index' => $indexName,
                'type'  => $type,
                'body'  => [$type => $mapping],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function deleteByQuery(array $params)
    {
        $this->getClient()->deleteByQuery($params);
    }

    /**
     * Initialize, if not initialized yet, and return ES client instance
     *
     * @return \Elasticsearch\Client
     * @throws ConnectionDisabledException
     */
    private function getClient()
    {
        if (!$this->config->isEnabled()) {
            throw new ConnectionDisabledException(__('ElasticSearch indexer disabled.'));
        }

        if (!$this->client instanceof \Elasticsearch\Client) {
            $this->client = $this->clientBuilder->build($this->clientConfiguration->getOptions());
        }

        return $this->client;
    }
}
