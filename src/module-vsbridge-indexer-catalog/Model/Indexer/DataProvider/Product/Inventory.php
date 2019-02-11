<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product;

use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeIndexerCatalog\Model\InventoryProcessor;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Inventory as Resource;

/**
 * Class Inventory
 */
class Inventory implements DataProviderInterface
{

    /**
     * @var \Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Inventory
     */
    private $resourceModel;

    /**
     * @var InventoryProcessor
     */
    private $inventoryProcessor;

    /**
     * Inventory constructor.
     *
     * @param Resource $resource
     * @param InventoryProcessor $inventoryProcessor
     */
    public function __construct(
        Resource $resource,
        InventoryProcessor $inventoryProcessor
    ) {
        $this->resourceModel = $resource;
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
        $inventoryData = $this->resourceModel->loadInventoryData($storeId, array_keys($indexData));

        foreach ($inventoryData as $inventoryDataRow) {
            $productId = (int) $inventoryDataRow['product_id'];
            $indexData[$productId]['stock'] =
                $this->inventoryProcessor->prepareInventoryData($storeId, $inventoryDataRow);
        }

        $inventoryData = null;

        return $indexData;
    }
}
