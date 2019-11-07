<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Marcin Dykas <mdykas@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types=1);

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product;

use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeIndexerCatalog\Api\CatalogConfigurationInterface;
use Divante\VsbridgeIndexerCatalog\Model\Product\ParentResolver;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Bundle\Model\Product\Type as BundleType;

/**
 * Class ParentData
 */
class ParentSku implements DataProviderInterface
{
    const FIELD_NAME = 'parent_sku';

    /**
     * @var GroupedType
     */
    private $groupedType;

    /**
     * @var ConfigurableType
     */
    private $configurableType;

    /**
     * @var BundleType
     */
    private $bundleType;

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
     * @param GroupedType $groupedType
     * @param ConfigurableType $configurableType
     * @param BundleType $bundleType
     * @param ParentResolver $parentResolver
     * @param CatalogConfigurationInterface $configSettings
     *
     */
    public function __construct(
        GroupedType $groupedType,
        ConfigurableType $configurableType,
        BundleType $bundleType,
        ParentResolver $parentResolver,
        CatalogConfigurationInterface $configSettings
    ) {
        $this->groupedType = $groupedType;
        $this->configurableType = $configurableType;
        $this->bundleType = $bundleType;
        $this->parentResolver = $parentResolver;
        $this->configSettings = $configSettings;
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        if (!$this->configSettings->addParentSku()) {
            return $indexData;
        }

        foreach ($indexData as $productId => $productData) {
            if ($productData['type_id'] == 'simple') {
                $groupedParentIds = $this->groupedType->getParentIdsByChild($productId);
                $configurableParentIds = $this->configurableType->getParentIdsByChild($productId);
                $bundleParentIds = $this->bundleType->getParentIdsByChild($productId);

                $parentIds = $groupedParentIds + $configurableParentIds + $bundleParentIds;

                if (!empty($parentIds)) {
                    $parentSkus = $this->parentResolver->getParentProductsByIds($parentIds);

                    $indexData[$productId][self::FIELD_NAME] = $parentSkus;
                }
            }
        }

        return $indexData;
    }
}
