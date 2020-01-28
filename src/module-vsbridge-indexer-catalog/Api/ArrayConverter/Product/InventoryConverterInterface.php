<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Api\ArrayConverter\Product;

/**
 * Interface InventoryConverterInterface
 */
interface InventoryConverterInterface
{
    /**
     * @param int $storeId
     * @param array $inventory
     *
     * @return array
     */
    public function prepareInventoryData(int $storeId, array $inventory): array;
}
