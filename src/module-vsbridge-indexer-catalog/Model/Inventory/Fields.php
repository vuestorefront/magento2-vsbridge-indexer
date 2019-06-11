<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types=1);

namespace Divante\VsbridgeIndexerCatalog\Model\Inventory;

/**
 * Class Fields
 */
class Fields
{
    /**
     * @var array
     */
    private $childRequiredFields = [
        'product_id',
        'is_in_stock',
        'min_qty',
        'notify_stock_qty',
        'use_config_notify_stock_qty',
        'qty',
    ];

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
     * @return array
     */
    public function getRequiredColumns(): array
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getChildRequiredColumns(): array
    {
        return $this->childRequiredFields;
    }
}
