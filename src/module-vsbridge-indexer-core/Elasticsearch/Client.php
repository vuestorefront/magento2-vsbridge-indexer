<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Elasticsearch;

use Divante\VsbridgeIndexerCore\Api\Client\ClientInterface;
use Divante\VsbridgeIndexerCore\Exception\ConnectionDisabledException;

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
                'include_type_name' => false,
                'index' => $indexName,
                'body'  => $mapping,
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
     * @return \Elasticsearch\Client
     * @throws ConnectionDisabledException
     */
    private function getClient()
    {
        return $this->client;
    }
}
