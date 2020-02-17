<?php

declare(strict_types=1);

/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product;

use Divante\VsbridgeIndexerCatalog\Api\CatalogConfigurationInterface;
use Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product\AttributesMetadata\GetProductValues;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\AttributeMetadata as Resource;
use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;

/**
 * Class AttributesMetadata
 */
class AttributesMetadata  implements DataProviderInterface
{
    /**
     * @var Resource
     */
    private $resourceModel;

    /**
     * @var GetProductValues
     */
    private $getProductValues;

    /**
     * @var CatalogConfigurationInterface
     */
    private $configuration;

    /**
     * AttributesMetadata constructor.
     *
     * @param GetProductValues $getProductValues
     * @param CatalogConfigurationInterface $configuration
     * @param Resource $resourceModel
     */
    public function __construct(
        GetProductValues $getProductValues,
        CatalogConfigurationInterface $configuration,
        Resource $resourceModel
    ) {
        $this->resourceModel = $resourceModel;
        $this->getProductValues = $getProductValues;
        $this->configuration = $configuration;
    }

    /**
     * @param array $indexData
     * @param int $storeId
     *
     * @return array
     */
    public function addData(array $indexData, $storeId)
    {
        if ($this->configuration->canExportAttributesMetadata()) {
            foreach ($indexData as $productId => $productDTO) {
                $metaAttributes = $this->getAttributeMetadata($productDTO, $storeId);
                $indexData[$productId]['attributes_metadata'] = $metaAttributes;
            }
        }

        return $indexData;
    }

    /**
     * @param array $productDTO
     * @param int $storeId
     *
     * @return array
     */
    private function getAttributeMetadata(array $productDTO, int $storeId): array
    {
        $attributes = $this->resourceModel->getAttributes($storeId);
        $meta = [];

        foreach ($attributes as $attribute) {
            $options = $this->getProductOptions($productDTO, $attribute, $storeId);
            $attributeData = $attribute;
            unset($attributeData['source_model']);
            $storeLabel = $this->resourceModel->getStoreLabels($attribute['id'], $storeId);

            if ($storeLabel) {
                $attributeData['default_frontend_label'] = $storeLabel;
            }

            $attributeData['options'] = $options;
            $meta[] = $attributeData;
        }

        return $meta;
    }

    /**
     * @param array $productDTO
     * @param array $attribute
     * @param int $storeId
     *
     * @return array
     */
    private function getProductOptions(array $productDTO, array $attribute, int $storeId): array
    {
        return $this->getOptionsForOptionAttributes($productDTO, $attribute, $storeId);
    }

    /**
     * @param array $productDTO
     * @param array $attribute
     * @param int $storeId
     *
     * @return array
     */
    private function getOptionsForOptionAttributes(array $productDTO, array $attribute, int $storeId)
    {
        $attributeId = $attribute['attribute_id'];
        $attributeCode = $attribute['attribute_code'];
        $allOptions = $this->resourceModel->getOptions($attributeId, $storeId);

        if (empty($allOptions)) {
            return [];
        }

        $options = $this->getProductValues->execute($productDTO, $attributeCode);
        $productOptions = [];

        foreach ($options as $optionId) {
            if (isset($allOptions[$optionId])) {
                $productOptions[] = $allOptions[$optionId];
            }
        }

        return $productOptions;
    }
}
