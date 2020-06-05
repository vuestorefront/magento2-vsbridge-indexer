<?php

declare(strict_types=1);

namespace Divante\VsbridgeIndexerCatalog\Model\Attributes;

use Divante\VsbridgeIndexerCatalog\Model\SystemConfig\CategoryConfigInterface;

/**
 * Class CategoryAttributes
 */
class CategoryAttributes
{
    /**
     * @const
     */
    const MINIMAL_ATTRIBUTE_SET = [
        'name',
        'is_active',
        'url_path',
        'url_key',
    ];

    /**
     * @var CategoryConfigInterface
     */
    private $config;

    /**
     * CategoryChildAttributes constructor.
     *
     * @param CategoryConfigInterface $categoryConfig
     */
    public function __construct(CategoryConfigInterface $categoryConfig)
    {
        $this->config = $categoryConfig;
    }

    /**
     * Retrieve required attributes for category
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getRequiredAttributes(int $storeId)
    {
        $attributes = $this->config->getAllowedAttributesToIndex($storeId);

        if (!empty($attributes)) {
            $attributes = array_merge($attributes, self::MINIMAL_ATTRIBUTE_SET);

            return array_unique($attributes);
        }

        return $attributes;
    }

    /**
     * @param int $storeId
     *
     * @return bool
     */
    public function canAddAvailableSortBy(int $storeId): bool
    {
        return $this->isAttributeAllowed('available_sort_by', $storeId);
    }

    /**
     * @param int $storeId
     *
     * @return bool
     */
    public function canAddDefaultSortBy(int $storeId): bool
    {
        return $this->isAttributeAllowed('default_sort_by', $storeId);
    }

    /**
     * @param string $attributeCode
     * @param int $storeId
     *
     * @return bool
     */
    private function isAttributeAllowed(string $attributeCode, int $storeId): bool
    {
        $allowedAttributes = $this->getRequiredAttributes($storeId);

        return empty($allowedAttributes) || in_array($attributeCode, $allowedAttributes);
    }
}
