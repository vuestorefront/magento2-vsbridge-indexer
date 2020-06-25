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
    public function createIndex(string $indexName, array $indexSettings);

    /**
     * Retrieve the list of all index having a specified alias.
     *
     * @param string $indexAlias Index alias.
     *
     * @return string[]
     */
    public function getIndicesNameByAlias(string $indexAlias): array;

    /**
     * @param array $aliasActions
     *
     * @return void
     */
    public function updateAliases(array $aliasActions);

    /**
     * @param string $indexName
     *
     * @return void
     * @throws ConnectionDisabledException
     */
    public function refreshIndex(string $indexName);

    /**
     * @param string $indexName
     *
     * @return bool
     * @throws ConnectionDisabledException
     */
    public function indexExists(string $indexName);

    /**
     * @param string $indexName
     *
     * @return array
     * @throws ConnectionDisabledException
     */
    public function deleteIndex(string $indexName);

    /**
     * @param string $indexName
     * @param string $type
     * @param array  $mapping
     *
     * @throws ConnectionDisabledException
     */
    public function putMapping(string $indexName, string $type, array $mapping);

    /**
     * @param array $params
     *
     * @throws ConnectionDisabledException
     */
    public function deleteByQuery(array $params);
}
