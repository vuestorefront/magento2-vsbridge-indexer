<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Api\Client;

/**
 * Interface ClientInterface
 */
interface ClientInterface
{
    /**
     * @param array $bulkParams
     *
     * @return array
     */
    public function bulk(array $bulkParams);

    /**
     * @param string $indexName
     * @param array $indexSettings
     *
     * @return void
     */
    public function createIndex($indexName, array $indexSettings);

    /**
     * @param string $indexName
     *
     * @return void
     */
    public function refreshIndex($indexName);

    /**
     * @param string $indexName
     *
     * @return bool
     */
    public function indexExists($indexName);

    /**
     * @param string $indexName
     *
     * @return array
     */
    public function deleteIndex($indexName);

    /**
     * @param string $indexName
     * @param string $type
     * @param array $mapping
     */
    public function putMapping($indexName, $type, array $mapping);

    /**
     * @param array $params
     */
    public function deleteByQuery(array $params);
}
