<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Inventory
 */
class Inventory
{

    /**
     * @var array
     */
    private $fields = [
        'product_id',
        'item_id',
        'stock_id',
        'qty',
        'is_in_stock',
        'is_qty_decimal',
        'use_config_min_qty',
        'min_qty',
        'use_config_min_sale_qty',
        'min_sale_qty',
        'use_config_max_sale_qty',
        'max_sale_qty',
        'use_config_notify_stock_qty',
        'notify_stock_qty',
        'use_config_qty_increments',
        'backorders',
        'use_config_backorders',
        'qty_increments',
        'use_config_enable_qty_inc',
        'enable_qty_increments',
        'use_config_manage_stock',
        'manage_stock',
        'low_stock_date',
    ];

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Inventory constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resourceModel
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ResourceConnection $resourceModel
    ) {
        $this->resource = $resourceModel;
        $this->storeManager = $storeManager;
    }

    /**
     * @param int $storeId
     * @param array $productIds
     *
     * @return array
     */
    public function loadInventoryData($storeId, array $productIds)
    {
        return $this->getInventoryData($storeId, $productIds, $this->fields);
    }

    /**
     * @param       $storeId
     * @param array $productIds
     *
     * @return array
     */
    public function loadChildrenData($storeId, array $productIds)
    {
        $fields = [
            'product_id',
            'is_in_stock',
            'min_qty',
            'notify_stock_qty',
            'use_config_notify_stock_qty',
            'qty',
        ];

        return $this->getInventoryData($storeId, $productIds, $fields);
    }

    /**
     * @param int $storeId
     * @param array $productIds
     * @param array $fields
     *
     * @return array
     */
    private function getInventoryData($storeId, array $productIds, array $fields)
    {
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        $connection = $this->resource->getConnection();

        $select = $connection->select()
            ->from(
                ['main_table' => $this->resource->getTableName('cataloginventory_stock_item')],
                $fields
            )->where('main_table.product_id IN (?)', $productIds);

        $joinConditionClause = [
            'main_table.product_id=status_table.product_id',
            'main_table.stock_id=status_table.stock_id',
            'status_table.website_id = ?'
        ];

        $select->joinLeft(
            ['status_table' => $this->resource->getTableName('cataloginventory_stock_status')],
            $connection->quoteInto(
                implode(' AND ', $joinConditionClause),
                $websiteId
            ),
            ['stock_status']
        );

        return $connection->fetchAssoc($select);
    }
}
