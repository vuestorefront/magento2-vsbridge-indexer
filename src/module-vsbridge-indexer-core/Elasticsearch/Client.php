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

/**
 * Class Client
 */
class Client implements ClientInterface
{

    /**
     * @var \Elasticsearch\Client
     */
    private $esClient = null;

    /**
     * Client constructor.
     *
     * @param ClientBuilder $clientBuilder
     * @param ClientConfiguration $clientConfiguration
     */
    public function __construct(ClientBuilder $clientBuilder, ClientConfiguration $clientConfiguration)
    {
        $this->esClient = $clientBuilder->build($clientConfiguration->getOptions());
    }

    /**
     * @inheritdoc
     */
    public function bulk(array $bulkParams)
    {
        return $this->esClient->bulk($bulkParams);
    }

    /**
     * @inheritdoc
     */
    public function createIndex($indexName, array $indexSettings)
    {
        $this->esClient->indices()->create(
            [
                'index' => $indexName,
                'body' => $indexSettings,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function refreshIndex($indexName)
    {
        $this->esClient->indices()->refresh(['index' => $indexName]);
    }

    /**
     * @inheritdoc
     */
    public function indexExists($indexName)
    {
        return $this->esClient->indices()->exists(['index' => $indexName]);
    }

    /**
     * @inheritdoc
     */
    public function deleteIndex($indexName)
    {
        return $this->esClient->indices()->delete(['index' => $indexName]);
    }

    /**
     * @inheritdoc
     */
    public function putMapping($indexName, $type, array $mapping)
    {
        $this->esClient->indices()->putMapping(
            [
                'index' => $indexName,
                'type' => $type,
                'body' => [$type => $mapping],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function deleteByQuery(array $params)
    {
        $this->esClient->deleteByQuery($params);
    }
}
