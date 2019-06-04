<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model;

use Divante\VsbridgeIndexerCatalog\Api\Data\CatalogConfigurationInterface;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Config as ConfigResource;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;

/**
 * Class ConfigSettings
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
     * @var ConfigResource
     */
    private $catalogConfigResource;

    /**
     * ClientConfiguration constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigResource $configResource
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ConfigResource $configResource
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->catalogConfigResource = $configResource;
    }

    /**
     * @inheritdoc
     */
    public function useMagentoUrlKeys()
    {
        return (bool) $this->getConfigParam('use_magento_url_keys');
    }

    /**
     * @inheritdoc
     */
    public function syncTierPrices()
    {
        return (bool) $this->getConfigParam('sync_tier_prices');
    }

    /**
     * @inheritdoc
     */
    public function getAllowedProductTypes($storeId)
    {
        $types = $this->getConfigParam('allowed_product_types', $storeId);

        if (null === $types || '' === $types) {
            $types = [];
        } else {
            $types = explode(',', $types);
        }

        return $types;
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

            if ($storeId) {
                $configValue = $this->scopeConfig->getValue($path, 'stores', $storeId);
            }

            $configValue = $this->scopeConfig->getValue($path);
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
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );

            $this->settings[$key] = (string) $sortBy;
        }

        return $this->settings[$key];
    }
}
