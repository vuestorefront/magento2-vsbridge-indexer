<?php declare(strict_types=1);
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\SystemConfig;

use Divante\VsbridgeIndexerCatalog\Api\CatalogConfigurationInterface;
use Divante\VsbridgeIndexerCatalog\Model\Product\GetAttributeCodesByIds;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class CatalogConfig
 */
class CatalogConfig implements CatalogConfigurationInterface
{
    /**
     * @var array
     */
    private $settings = [];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var GetAttributeCodesByIds
     */
    private $getAttributeCodesByIds;

    /**
     * Settings constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param GetAttributeCodesByIds $getAttributeCodesByIds
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        GetAttributeCodesByIds $getAttributeCodesByIds
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->getAttributeCodesByIds = $getAttributeCodesByIds;
    }

    /**
     * @inheritDoc
     *
     * @return bool
     */
    public function useMagentoUrlKeys(): bool
    {
        return (bool) $this->getConfigParam(CatalogConfigurationInterface::USE_MAGENTO_URL_KEYS);
    }

    /**
     * @inheritDoc
     *
     * @return bool
     */
    public function useUrlKeyToGenerateSlug(): bool
    {
        return (bool) $this->getConfigParam(CatalogConfigurationInterface::USE_URL_KEY_TO_GENERATE_SLUG);
    }

    /**
     * @inheritDoc
     *
     * @return bool
     */
    public function useCatalogRules(): bool
    {
        return (bool) $this->getConfigParam(CatalogConfigurationInterface::USE_CATALOG_RULES);
    }

    /**
     * @inheritDoc
     *
     * @return bool
     */
    public function syncTierPrices(): bool
    {
        return (bool) $this->getConfigParam(CatalogConfigurationInterface::SYNC_TIER_PRICES);
    }

    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function addParentSku(): bool
    {
        return (bool) $this->getConfigParam('add_parent_sku');
    }

    /**
     * @inheritDoc
     *
     * @return bool
     */
    public function addSwatchesToConfigurableOptions(): bool
    {
        return (bool) $this->getConfigParam(CatalogConfigurationInterface::ADD_SWATCHES_OPTIONS);
    }

    /**
     * @inheritDoc
     *
     * @return bool
     */
    public function canExportAttributesMetadata(): bool
    {
        return (bool) $this->getConfigParam(CatalogConfigurationInterface::EXPORT_ATTRIBUTES_METADATA);
    }

    /**
     * @inheritDoc
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getAllowedProductTypes($storeId): array
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
     * @inheritDoc
     *
     * @param int $storeId
     *
     * @return array
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
     * @inheritDoc
     *
     * @param int $storeId
     *
     * @return array
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
     * @inheritDoc
     *
     * @param int $storeId
     *
     * @return int
     */
    public function getConfigurableChildrenBatchSize(int $storeId): int
    {
        return (int) $this->getConfigParam(
            CatalogConfigurationInterface::CONFIGURABLE_CHILDREN_BATCH_SIZE,
            $storeId
        );
    }

    /**
     * Retrieve config value by path and scope.
     *
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
}
