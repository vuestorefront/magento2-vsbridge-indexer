<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Api;

/**
 * Interface DataProviderInterface
 */
interface DataProviderInterface
{
    /**
     * Append data to a list of documents.
     *
     * @param array $indexData
     * @param int $storeId
     *
     * @return array
     */
    public function addData(array $indexData, $storeId);
}
