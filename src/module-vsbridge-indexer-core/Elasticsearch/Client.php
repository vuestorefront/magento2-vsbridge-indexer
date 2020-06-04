<?php

namespace Divante\VsbridgeIndexerCore\Elasticsearch;

use Divante\VsbridgeIndexerCore\Api\Client\ClientInterface;

/**
 * Class Client
 */
class Client implements ClientInterface
{
    /**
     * @var \Elasticsearch\Client
     */
    private $client;

    /**
     * Client constructor.
     *
     * @param \Elasticsearch\Client $client
     */
    public function __construct(\Elasticsearch\Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param array $bulkParams
     *
     * @return array
     */
    public function bulk(array $bulkParams)
    {
        return $this->client->bulk($bulkParams);
    }

    /**
     * @param $indexName
     * @param array $indexSettings
     */
    public function createIndex(string $indexName, array $indexSettings)
    {
        $this->client->indices()->create(
            [
                'index' => $indexName,
                'body'  => $indexSettings,
            ]
        );
    }

    /**
     * @param string $indexAlias
     *
     * @return array
     */
    public function getIndicesNameByAlias(string $indexAlias): array
    {
        $indices = [];

        try {
            $indices = $this->client->indices()->getMapping(['index' => $indexAlias]);
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
        }

        return array_keys($indices);
    }

    /**
     * @param array $aliasActions
     */
    public function updateAliases(array $aliasActions)
    {
        $this->client->indices()->updateAliases(['body' => ['actions' => $aliasActions]]);
    }

    /**
     * @param string $indexName
     */
    public function refreshIndex(string $indexName)
    {
        $this->client->indices()->refresh(['index' => $indexName]);
    }

    /**
     * @param string $indexName
     *
     * @return bool
     */
    public function indexExists(string $indexName)
    {
        return $this->client->indices()->exists(['index' => $indexName]);
    }

    /**
     * @param string $indexName
     *
     * @return array
     */
    public function deleteIndex(string $indexName)
    {
        return $this->client->indices()->delete(['index' => $indexName]);
    }

    /**
     * @param string $indexName
     * @param string $type
     * @param array $mapping
     */
    public function putMapping(string $indexName, string $type, array $mapping)
    {
        $this->client->indices()->putMapping(
            [
                'index' => $indexName,
                'type'  => $type,
                'body'  => [$type => $mapping],
            ]
        );
    }

    /**
     * @param array $params
     */
    public function deleteByQuery(array $params)
    {
        $this->client->deleteByQuery($params);
    }
}
