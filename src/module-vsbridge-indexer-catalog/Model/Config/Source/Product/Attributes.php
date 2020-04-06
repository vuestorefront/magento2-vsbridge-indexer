<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Config\Source\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;

/**
 * Class Attributes
 */
class Attributes extends AbstractAttributeSource
{
    /**
     *
     */
    const GENERAL_RESTRICTED_ATTRIBUTES = [
        'sku',
        'url_path',
        'url_key',
        'name',
        'visibility',
        'status',
        'tier_price',
        'price',
        'price_type',
        'gallery',
        'status',
        'category_ids',
        'swatch_image',
        'quantity_and_stock_status',
        'options_container',
    ];

    /**
     * @inheritDoc
     *
     * @param ProductAttributeInterface $attribute
     *
     * @return bool
     */
    public function canAddAttribute(ProductAttributeInterface $attribute): bool
    {
        return !in_array($attribute->getAttributeCode(), self::GENERAL_RESTRICTED_ATTRIBUTES);
    }
}
