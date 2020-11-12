<?php

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\Action;

use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Attribute as ResourceModel;
use Divante\VsbridgeIndexerCatalog\Index\Mapping\Attribute as AttributeMapping;
use Divante\VsbridgeIndexerCore\Api\ConvertValueInterface;
use Divante\VsbridgeIndexerCore\Indexer\RebuildActionInterface;

/**
 * Class Attribute
 */
class Attribute implements RebuildActionInterface
{
    /**
     * @var ResourceModel
     */
    private $resourceModel;

    /**
     * @var AttributeMapping
     */
    private $attributeMapping;

    /**
     * @var ConvertValueInterface
     */
    private $convertValue;

    /**
     * Attribute constructor.
     *
     * @param ConvertValueInterface $convertValue
     * @param AttributeMapping $attributeMapping
     * @param ResourceModel $resourceModel
     */
    public function __construct(
        ConvertValueInterface $convertValue,
        AttributeMapping $attributeMapping,
        ResourceModel $resourceModel
    ) {
        $this->convertValue = $convertValue;
        $this->resourceModel = $resourceModel;
        $this->attributeMapping = $attributeMapping;
    }

    /**
     * @param int $storeId
     * @param array $attributeIds
     *
     * @return \Traversable
     */
    public function rebuild(int $storeId, array $attributeIds): \Traversable
    {
        $lastAttributeId = 0;

        do {
            $attributes = $this->resourceModel->getAttributes($attributeIds, $lastAttributeId);

            foreach ($attributes as $attributeData) {
                $lastAttributeId = $attributeData['attribute_id'];
                $attributeData['id'] = $attributeData['attribute_id'];
                $attributeData = $this->filterData($attributeData);

                yield $lastAttributeId => $attributeData;
            }
        } while (!empty($attributes));
    }

    /**
     * @param array $attributeData
     *
     * @return array
     */
    private function filterData(array $attributeData)
    {
        foreach ($attributeData as $key => $value) {
            $value = $this->convertValue->execute($this->attributeMapping, $key, $value);
            $attributeData[$key] = $value;
        }

        return $attributeData;
    }
}
