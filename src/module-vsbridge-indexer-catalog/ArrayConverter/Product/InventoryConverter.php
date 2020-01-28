<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\ArrayConverter\Product;

use Divante\VsbridgeIndexerCore\Index\Mapping\GeneralMapping;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Divante\VsbridgeIndexerCatalog\Api\ArrayConverter\Product\InventoryConverterInterface;

/**
 * Class InventoryConverter
 */
class InventoryConverter implements InventoryConverterInterface
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
     * InventoryConverter constructor.
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
    public function prepareInventoryData(int $storeId, array $inventory): array
    {
        if (!empty($inventory[StockItemInterface::USE_CONFIG_MIN_QTY])) {
            $inventory[StockItemInterface::MIN_QTY] = $this->stockConfiguration->getMinQty($storeId);
        }

        if (!empty($inventory[StockItemInterface::USE_CONFIG_MIN_SALE_QTY])) {
            $inventory[StockItemInterface::MIN_SALE_QTY] = $this->stockConfiguration->getMinSaleQty($storeId);
        }

        if (!empty($inventory[StockItemInterface::USE_CONFIG_MAX_SALE_QTY])) {
            $inventory[StockItemInterface::MAX_SALE_QTY] = $this->stockConfiguration->getMaxSaleQty($storeId);
        }

        if (!empty($inventory[StockItemInterface::USE_CONFIG_NOTIFY_STOCK_QTY])) {
            $inventory[StockItemInterface::NOTIFY_STOCK_QTY] = $this->stockConfiguration->getNotifyStockQty($storeId);
        }

        if (!empty($inventory[StockItemInterface::USE_CONFIG_QTY_INCREMENTS])) {
            $inventory[StockItemInterface::QTY_INCREMENTS] = $this->stockConfiguration->getQtyIncrements($storeId);
        }

        if (!empty($inventory[StockItemInterface::USE_CONFIG_ENABLE_QTY_INC])) {
            $inventory[StockItemInterface::ENABLE_QTY_INCREMENTS] = $this->stockConfiguration->getEnableQtyIncrements($storeId);
        }

        if (!empty($inventory[StockItemInterface::USE_CONFIG_BACKORDERS])) {
            $inventory[StockItemInterface::BACKORDERS] = $this->stockConfiguration->getBackorders($storeId);
        }

        return $this->generalMapping->prepareStockData($inventory);
    }
}
