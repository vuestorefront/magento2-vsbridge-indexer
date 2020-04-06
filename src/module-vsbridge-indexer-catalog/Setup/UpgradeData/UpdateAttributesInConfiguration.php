<?php

declare(strict_types=1);

namespace Divante\VsbridgeIndexerCatalog\Setup\UpgradeData;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class UpdateAttributesInConfiguration
 */
class UpdateAttributesInConfiguration
{
    /**
     * @var Config
     */
    private $resourceConfig;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var string
     */
    private $entityType;

    /**
     * UpdateAttributesInConfiguration constructor.
     *
     * @param Config $resourceConfig
     * @param EavConfig $eavConfig
     * @param string $entityType
     */
    public function __construct(
        Config $resourceConfig,
        EavConfig $eavConfig,
        string $entityType
    ) {
        $this->entityType = $entityType;
        $this->eavConfig = $eavConfig;
        $this->resourceConfig = $resourceConfig;
    }

    /**
     * @param array $attributeCodes
     * @param string $path
     *
     * @return void
     *
     * @throws LocalizedException
     */
    public function execute(array $attributeCodes, string $path)
    {
        $attributeIds = $this->getAttributeIdsByCodes($attributeCodes);

        if (!empty($attributeIds)) {
            $this->resourceConfig->saveConfig(
                $path,
                $attributeIds,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                0
            );
        }
    }

    /**
     * @param array $attributes
     *
     * @return string
     * @throws LocalizedException
     */
    private function getAttributeIdsByCodes(array $attributes): string
    {
        $attributeIds = [];

        foreach ($attributes as $attributeCode) {
            $attribute = $this->eavConfig->getAttribute($this->entityType, $attributeCode);

            if ($attribute->getId()) {
                $attributeIds[] = $attribute->getId();
            }
        }

        return implode(',', $attributeIds);
    }
}
