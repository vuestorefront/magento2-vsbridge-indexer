<?php declare(strict_types=1);
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\SystemConfig;

use Divante\VsbridgeIndexerCatalog\Model\Category\GetAttributeCodesByIds;
use Magento\Catalog\Model\Config;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\ProductConfig as ConfigResource;

/**
 * Class CategoryConfig
 */
class CategoryConfig implements CategoryConfigInterface
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
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getAllowedAttributesToIndex(int $storeId): array
    {
        $cacheKey = sprintf('allowed_attributes_%d', $storeId);

        if (isset($this->settings[$cacheKey])) {
            return $this->settings[$cacheKey];
        }

        $attributes = (string)$this->getConfigParam(
            CategoryConfigInterface::CATEGORY_ATTRIBUTES,
            $storeId
        );

        $this->settings[$cacheKey] = $this->getAttributeCodesByIds->execute($attributes);

        return $this->settings[$cacheKey];
    }

    /**
     * @inheritdoc
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getAllowedChildAttributesToIndex(int $storeId): array
    {
        $cacheKey = sprintf('child_allowed_attributes_%d', $storeId);

        if (isset($this->settings[$cacheKey])) {
            return $this->settings[$cacheKey];
        }

        $attributes = (string)$this->getConfigParam(
            CategoryConfigInterface::CHILD_ATTRIBUTES,
            $storeId
        );

        $this->settings[$cacheKey] = $this->getAttributeCodesByIds->execute($attributes);

        return $this->getAttributeCodesByIds->execute($attributes);
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getAttributesUsedForSortBy(): array
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
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getProductListDefaultSortBy(int $storeId): string
    {
        $path = Config::XML_PATH_LIST_DEFAULT_SORT_BY;
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
     * @inheritdoc
     *
     * @param int $storeId
     *
     * @return string
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
            $this->settings[$key] = (string) $configValue;
        }

        return $this->settings[$key];
    }

    /**
     * Retrieve config value by path and scope.
     *
     * @param string $configField
     * @param int|null $storeId
     *
     * @return string|null
     */
    private function getConfigParam(string $configField, int $storeId = null)
    {
        $key = $configField . (string) $storeId;

        if (!isset($this->settings[$key])) {
            $path = CategoryConfigInterface::CATEGORY_SETTINGS_XML_PREFIX . '/' . $configField;
            $scopeType = ($storeId) ? ScopeInterface::SCOPE_STORES : ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

            $configValue = $this->scopeConfig->getValue($path, $scopeType, $storeId);
            $this->settings[$key] = $configValue;
        }

        return $this->settings[$key];
    }
}
