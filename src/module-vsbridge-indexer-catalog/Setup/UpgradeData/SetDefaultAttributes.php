<?php declare(strict_types=1);
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Setup\UpgradeData;

use Divante\VsbridgeIndexerCatalog\Api\CatalogConfigurationInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class SetDefaultAttributes
 */
class SetDefaultAttributes
{
    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var Config
     */
    private $resourceConfig;

    /**
     * @var array
     */
    private $productAttributes;

    /**
     * @var array
     */
    private $childAttributes;

    /**
     * SetDefaultAttributes constructor.
     *
     * @param EavConfig $eavConfig
     * @param Config $resourceConfig
     * @param array $productAttributes
     * @param array $childAttributes
     */
    public function __construct(
        EavConfig $eavConfig,
        Config $resourceConfig,
        array $productAttributes,
        array $childAttributes
    ) {
        $this->eavConfig = $eavConfig;
        $this->resourceConfig = $resourceConfig;
        $this->childAttributes = $childAttributes;
        $this->productAttributes = $productAttributes;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $attributeIds = $this->getAttributeIdsByCodes($this->productAttributes);
        $childAttributeIds = $this->getAttributeIdsByCodes($this->childAttributes);

        if (!empty($attributeIds)) {
            $this->resourceConfig->saveConfig(
                $this->getConfigPath(CatalogConfigurationInterface::PRODUCT_ATTRIBUTES),
                $attributeIds,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                0
            );
        }

        if (!empty($childAttributeIds)) {
            $this->resourceConfig->saveConfig(
                $this->getConfigPath(CatalogConfigurationInterface::CHILD_ATTRIBUTES),
                $childAttributeIds,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                0
            );
        }
    }

    /**
     * @param $configField
     *
     * @return string
     */
    private function getConfigPath($configField): string
    {
        return CatalogConfigurationInterface::CATALOG_SETTINGS_XML_PREFIX . '/' . $configField;
    }

    /**
     * @param array $attributes
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributeIdsByCodes(array $attributes): string
    {
        $attributeIds = [];

        foreach ($attributes as $attributeCode) {
            $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);

            if ($attribute->getId()) {
                $attributeIds[] = $attribute->getId();
            }
        }

        return implode(',', $attributeIds);
    }
}
