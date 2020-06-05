<?php declare(strict_types=1);
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Setup\UpgradeData;

use Divante\VsbridgeIndexerCatalog\Api\CatalogConfigurationInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class SetProductDefaultAttributes
 */
class SetProductDefaultAttributes
{
    /**
     * @var UpdateAttributesInConfigurationFactory
     */
    private $updateAttributesInConfiguration;

    /**
     * @var array
     */
    private $productAttributes;

    /**
     * @var array
     */
    private $childAttributes;

    /**
     * SetDefaultProductAttributes constructor.
     *
     * @param UpdateAttributesInConfigurationFactory $updateAttributesInConfiguration
     * @param array $productAttributes
     * @param array $childAttributes
     */
    public function __construct(
        UpdateAttributesInConfigurationFactory $updateAttributesInConfiguration,
        array $productAttributes,
        array $childAttributes
    ) {
        $this->childAttributes = $childAttributes;
        $this->productAttributes = $productAttributes;
        $this->updateAttributesInConfiguration = $updateAttributesInConfiguration;
    }

    /**
     * @throws LocalizedException
     */
    public function execute()
    {
        /** @var UpdateAttributesInConfiguration $updateConfiguration */
        $updateConfiguration = $this->updateAttributesInConfiguration->create(['entityType' => 'catalog_product']);

        $updateConfiguration->execute(
            $this->productAttributes,
            $this->getConfigPath(CatalogConfigurationInterface::PRODUCT_ATTRIBUTES)
        );

        $updateConfiguration->execute(
            $this->childAttributes,
            $this->getConfigPath(CatalogConfigurationInterface::CHILD_ATTRIBUTES)
        );
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
}
