<?php declare(strict_types=1);
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model;

use Divante\VsbridgeIndexerCatalog\Api\CatalogConfigurationInterface;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\ProductConfig as ConfigResource;
use Divante\VsbridgeIndexerCatalog\Model\Product\GetAttributeCodesByIds;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Settings
 */
class Settings implements CatalogConfigurationInterface
{
    /**
     * @var array
     */
    private $settings = [];

    /**
     * @var array
     */
    private $attributesSortBy = [];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var GetAttributeCodesByIds
     */
    private $getAttributeCodesByIds;

    /**
     * @var ConfigResource
     */
    private $catalogConfigResource;

    /**
     * Settings constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param GetAttributeCodesByIds $getAttributeCodesByIds
     * @param ConfigResource $configResource
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        GetAttributeCodesByIds $getAttributeCodesByIds,
        ConfigResource $configResource
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->getAttributeCodesByIds = $getAttributeCodesByIds;
        $this->catalogConfigResource = $configResource;
    }

    /**
     * @inheritdoc
     */
    public function useMagentoUrlKeys()
    {
        return (bool) $this->getConfigParam(CatalogConfigurationInterface::USE_MAGENTO_URL_KEYS);
    }

    /**
     * @inheritdoc
     */
    public function useUrlKeyToGenerateSlug()
    {
        return (bool) $this->getConfigParam(CatalogConfigurationInterface::USE_URL_KEY_TO_GENERATE_SLUG);
    }

    /***
     * @inheritdoc
     */
    public function useCatalogRules()
    {
        return (bool) $this->getConfigParam(CatalogConfigurationInterface::USE_CATALOG_RULES);
    }

    /**
     * @inheritdoc
     */
    public function syncTierPrices()
    {
        return (bool) $this->getConfigParam(CatalogConfigurationInterface::SYNC_TIER_PRICES);
    }

    /**
     * @return bool
     */
    public function addSwatchesToConfigurableOptions()
    {
        return (bool) $this->getConfigParam(CatalogConfigurationInterface::ADD_SWATCHES_OPTIONS);
    }

    /**
     * @return bool
     */
    public function canExportAttributesMetadata(): bool
    {
        return (bool) $this->getConfigParam(CatalogConfigurationInterface::EXPORT_ATTRIBUTES_METADATA);
    }

    /**
     * @inheritdoc
     */
    public function getAllowedProductTypes($storeId)
    {
        $types = $this->getConfigParam(CatalogConfigurationInterface::ALLOWED_PRODUCT_TYPES, $storeId);

        if (null === $types || '' === $types) {
            $types = [];
        } else {
            $types = explode(',', $types);
        }

        return $types;
    }

    /**
     * @inheritdoc
     */
    public function getAllowedAttributesToIndex(int $storeId): array
    {
        $attributes = (string)$this->getConfigParam(
            CatalogConfigurationInterface::PRODUCT_ATTRIBUTES,
            $storeId
        );

        return $this->getAttributeCodesByIds->execute($attributes);
    }

    /**
     * @inheritdoc
     */
    public function getAllowedChildAttributesToIndex(int $storeId): array
    {
        $attributes = (string)$this->getConfigParam(
            CatalogConfigurationInterface::CHILD_ATTRIBUTES,
            $storeId
        );

        return $this->getAttributeCodesByIds->execute($attributes);
    }

    /**
     * @param string $configField
     * @param int|null $storeId
     *
     * @return string|null
     */
    private function getConfigParam(string $configField, $storeId = null)
    {
        $key = $configField . (string) $storeId;

        if (!isset($this->settings[$key])) {
            $path = CatalogConfigurationInterface::CATALOG_SETTINGS_XML_PREFIX . '/' . $configField;
            $scopeType = ($storeId) ? ScopeInterface::SCOPE_STORES : ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

            $configValue = $this->scopeConfig->getValue($path, $scopeType, $storeId);
            $this->settings[$key] = $configValue;
        }

        return $this->settings[$key];
    }

    /**
     * @inheritdoc
     */
    public function getAttributesUsedForSortBy()
    {
        if (empty($this->attributesSortBy)) {
            $attributes = $this->catalogConfigResource->getAttributesUsedForSortBy();
            $attributes[] = 'position';

            $this->attributesSortBy = $attributes;
        }

        return $this->attributesSortBy;
    }

    /**
     * @inheritdoc
     */
    public function getProductListDefaultSortBy($storeId)
    {
        $path = \Magento\Catalog\Model\Config::XML_PATH_LIST_DEFAULT_SORT_BY;
        $key = $path . (string) $storeId;

        if (!isset($this->settings[$key])) {
            $sortBy = $this->scopeConfig->getValue(
                $path,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $this->settings[$key] = (string) $sortBy;
        }

        return $this->settings[$key];
    }

    /**
     * @inheritDoc
     */
    public function getCategoryUrlSuffix(int $storeId): string
    {
        $key = sprintf(
            '%s_%s',
            CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
            $storeId
        );

        if (!isset($this->settings[$key])) {
            $configValue = $this->scopeConfig->getValue(
                CategoryUrlPathGenerator::XML_PATH_CATEGORY_URL_SUFFIX,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $this->settings[$key] = $configValue;
        }

        return $this->settings[$key];
    }
}
