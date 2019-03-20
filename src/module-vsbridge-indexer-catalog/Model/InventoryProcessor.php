<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model;

use Divante\VsbridgeIndexerCore\Index\Mapping\GeneralMapping;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;

/**
 * Class InventoryProcessor
 */
class InventoryProcessor
{

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var GeneralMapping
     */
    private $generalMapping;

    /**
     * InventoryProcessor constructor.
     *
     * @param GeneralMapping $generalMapping
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        GeneralMapping $generalMapping,
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->generalMapping = $generalMapping;
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * @param int $storeId
     * @param array $inventory
     *
     * @return array
     */
    public function prepareInventoryData($storeId, array $inventory)
    {
        if (!empty($inventory[StockItemInterface::USE_CONFIG_MIN_QTY])) {
            $inventory['min_qty'] = $this->stockConfiguration->getMinQty($storeId);
        }

        if (!empty($inventory[StockItemInterface::USE_CONFIG_MIN_SALE_QTY])) {
            $inventory['max_sale_qty'] = $this->stockConfiguration->getMinSaleQty($storeId);
        }

        if (!empty($inventory[StockItemInterface::USE_CONFIG_MAX_SALE_QTY])) {
            $inventory['max_sale_qty'] = $this->stockConfiguration->getMaxSaleQty($storeId);
        }

        if (!empty($inventory[StockItemInterface::USE_CONFIG_NOTIFY_STOCK_QTY])) {
            $inventory['notify_stock_qty'] = $this->stockConfiguration->getNotifyStockQty($storeId);
        }

        if (!empty($inventory[StockItemInterface::USE_CONFIG_QTY_INCREMENTS])) {
            $inventory['qty_increments'] = $this->stockConfiguration->getQtyIncrements($storeId);
        }

        if (!empty($inventory[StockItemInterface::USE_CONFIG_ENABLE_QTY_INC])) {
            $inventory['enable_qty_increments'] = $this->stockConfiguration->getEnableQtyIncrements($storeId);
        }

        if (!empty($inventory[StockItemInterface::USE_CONFIG_BACKORDERS])) {
            $inventory['backorders'] = $this->stockConfiguration->getBackorders($storeId);
        }
        
        return $this->generalMapping->prepareStockData($inventory);
    }
}
