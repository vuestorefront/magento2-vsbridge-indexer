<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Api\Client;

use Divante\VsbridgeIndexerCore\Exception\ConnectionDisabledException;

/**
 * Interface ClientInterface
 */
interface ClientInterface
{
    /**
     * @param array $bulkParams
     *
     * @return array
     * @throws ConnectionDisabledException
     */
    public function bulk(array $bulkParams);

    /**
     * @param string $indexName
     * @param array  $indexSettings
     *
     * @return void
     * @throws ConnectionDisabledException
     */
    public function createIndex($indexName, array $indexSettings);

    /**
     * @param string $indexName
     *
     * @return void
     * @throws ConnectionDisabledException
     */
    public function refreshIndex($indexName);

    /**
     * @param string $indexName
     *
     * @return bool
     * @throws ConnectionDisabledException
     */
    public function indexExists($indexName);

    /**
     * @param string $indexName
     *
     * @return array
     * @throws ConnectionDisabledException
     */
    public function deleteIndex($indexName);

    /**
     * @param string $indexName
     * @param string $type
     * @param array  $mapping
     *
     * @throws ConnectionDisabledException
     */
    public function putMapping($indexName, $type, array $mapping);

    /**
     * @param array $params
     *
     * @throws ConnectionDisabledException
     */
    public function deleteByQuery(array $params);
}
