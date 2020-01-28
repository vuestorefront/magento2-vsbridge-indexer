<?php declare(strict_types=1);
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Api;

/**
 * Interface LoadTierPricesInterface
 */
interface LoadTierPricesInterface
{
    /**
     * @param array $indexData
     * @param int $storeId
     *
     * @return array
     */
    public function execute(array $indexData, int $storeId): array;
}
