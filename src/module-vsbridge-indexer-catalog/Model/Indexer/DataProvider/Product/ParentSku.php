<?php
/**
 * Copyright Divante Sp. z o.o.
 * See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types=1);

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product;

use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeIndexerCatalog\Api\CatalogConfigurationInterface;
use Divante\VsbridgeIndexerCatalog\Model\Product\ParentResolver;

/**
 * Class responsible for adding "parent_sku" for products
 */
class ParentSku implements DataProviderInterface
{
    /**
     * @const string
     */
    const FIELD_NAME = 'parent_sku';

    /**
     * @var ParentResolver
     */
    private $parentResolver;

    /**
     * @var CatalogConfigurationInterface
     */
    private $configSettings;

    /**
     * ParentData constructor.
     *
     * @param ParentResolver $parentResolver
     * @param CatalogConfigurationInterface $configSettings
     *
     */
    public function __construct(
        ParentResolver $parentResolver,
        CatalogConfigurationInterface $configSettings
    ) {
        $this->parentResolver = $parentResolver;
        $this->configSettings = $configSettings;
    }

    /**
     * @inheritDoc
     *
     * @param array $indexData
     * @param int $storeId
     *
     * @return array
     */
    public function addData(array $indexData, $storeId)
    {
        if (!$this->configSettings->addParentSku()) {
            return $indexData;
        }

        $childIds = array_keys($indexData);
        $this->parentResolver->load($childIds);

        foreach ($indexData as $productId => $productDTO) {
            $productDTO[self::FIELD_NAME] = $this->parentResolver->resolveParentSku($productId);
            $indexData[$productId] = $productDTO;
        }

        return $indexData;
    }
}
