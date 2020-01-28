<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product;

use Divante\VsbridgeIndexerCatalog\Api\LoadInventoryInterface;
use Divante\VsbridgeIndexerCatalog\Api\ArrayConverter\Product\InventoryConverterInterface;
use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;

/**
 * Class Inventory
 */
class Inventory implements DataProviderInterface
{

    /**
     * @var LoadInventoryInterface
     */
    private $getInventory;

    /**
     * @var InventoryConverterInterface
     */
    private $inventoryProcessor;

    /**
     * Inventory constructor.
     *
     * @param LoadInventoryInterface $getInventory
     * @param InventoryConverterInterface $inventoryProcessor
     */
    public function __construct(
        LoadInventoryInterface $getInventory,
        InventoryConverterInterface $inventoryProcessor
    ) {
        $this->getInventory = $getInventory;
        $this->inventoryProcessor = $inventoryProcessor;
    }

    /**
     * @param array $indexData
     * @param int   $storeId
     *
     * @return array
     */
    public function addData(array $indexData, $storeId)
    {
        $inventoryData = $this->getInventory->execute($indexData, $storeId);

        foreach ($inventoryData as $inventoryDataRow) {
            $productId = (int) $inventoryDataRow['product_id'];
            unset($inventoryDataRow['product_id']);
            $indexData[$productId]['stock'] =
                $this->inventoryProcessor->prepareInventoryData($storeId, $inventoryDataRow);
        }

        $inventoryData = null;

        return $indexData;
    }
}
