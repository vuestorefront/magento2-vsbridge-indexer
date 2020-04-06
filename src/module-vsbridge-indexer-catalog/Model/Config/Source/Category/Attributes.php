<?php

declare(strict_types=1);

namespace Divante\VsbridgeIndexerCatalog\Model\Config\Source\Category;

use Magento\Eav\Model\Entity\Attribute;

/**
 * Class Attributes
 */
class Attributes extends AbstractAttributeSource
{
    /**
     *
     */
    const RESTRICTED_ATTRIBUTES = [
        'all_children',
        'children',
        'children_count',
        'url_path',
        'url_key',
        'name',
        'is_active',
        'level',
        'path_in_store',
        'path',
        'position',
    ];

    /**
     * @inheritDoc
     *
     * @param Attribute $attribute
     *
     * @return bool
     */
    public function canAddAttribute(Attribute $attribute): bool
    {
        return !in_array($attribute->getAttributeCode(), self::RESTRICTED_ATTRIBUTES);
    }
}
