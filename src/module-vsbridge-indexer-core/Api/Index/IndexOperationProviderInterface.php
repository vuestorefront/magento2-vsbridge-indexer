<?php
/**
 * @package  Divante\VsbridgeIndexerCore
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Api\Index;

use Divante\VsbridgeIndexerCore\Api\IndexOperationInterface;
use Divante\VsbridgeIndexerCore\Exception\ConnectionDisabledException;

/**
 * Interface IndexOperationProviderInterface
 */
interface IndexOperationProviderInterface
{

    /**
     * Retrieve Index Operation per Store
     *
     * @param int $storeId
     *
     * @return IndexOperationInterface
     * @throws ConnectionDisabledException
     */
    public function getOperationByStore(int $storeId): IndexOperationInterface;
}
