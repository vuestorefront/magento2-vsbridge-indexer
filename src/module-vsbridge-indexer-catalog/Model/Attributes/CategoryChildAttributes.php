<?php

declare(strict_types=1);

namespace Divante\VsbridgeIndexerCatalog\Model\Attributes;

use Divante\VsbridgeIndexerCatalog\Model\SystemConfig\CategoryConfigInterface;

/**
 * Class CategoryChildrenAttributes
 */
class CategoryChildAttributes
{
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
     * Retrieve required attributes for child category
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getRequiredAttributes(int $storeId): array
    {
        $attributes = $this->config->getAllowedChildAttributesToIndex($storeId);

        if (!empty($attributes)) {
            $attributes = array_merge($attributes, CategoryAttributes::MINIMAL_ATTRIBUTE_SET);

            return array_unique($attributes);
        }

        return $attributes;
    }
}
