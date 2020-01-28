<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Config\Source;

/**
 * Class ProductAttributes
 */
class ProductAttributes extends AbstractProductAttributeSource
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
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     *
     * @return bool
     */
    public function canAddAttribute(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute): bool
    {
        return !in_array($attribute->getAttributeCode(), self::GENERAL_RESTRICTED_ATTRIBUTES);
    }
}
