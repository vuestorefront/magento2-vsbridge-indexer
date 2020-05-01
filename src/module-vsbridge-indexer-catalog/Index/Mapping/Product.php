<?php
/**
 * Copyright Divante Sp. z o.o.
 * See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Index\Mapping;

use Divante\VsbridgeIndexerCore\Api\Mapping\FieldInterface;
use Divante\VsbridgeIndexerCore\Api\MappingInterface;
use Divante\VsbridgeIndexerCore\Index\Mapping\GeneralMapping;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\LoadAttributes;
use Magento\Framework\Event\ManagerInterface as EventManager;

/**
 * Class Product
 */
class Product extends AbstractMapping implements MappingInterface
{
    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var GeneralMapping
     */
    private $generalMapping;

    /**
     * @var StockMapping
     */
    private $stockMapping;

    /**
     * @var LoadAttributes
     */
    private $resourceModel;

    /**
     * @var array
     */
    private $properties;

    /**
     * @var FieldMappingInterface[]
     */
    private $additionalMapping = [];

    /**
     * Product constructor.
     *
     * @param GeneralMapping $generalMapping
     * @param StockMapping $stockMapping
     * @param LoadAttributes $resourceModel
     * @param array $staticFieldMapping
     * @param array $additionalMapping
     */
    public function __construct(
        EventManager $eventManager,
        GeneralMapping $generalMapping,
        StockMapping $stockMapping,
        LoadAttributes $resourceModel,
        array $staticFieldMapping,
        array $additionalMapping
    ) {
        $this->eventManager = $eventManager;
        $this->stockMapping = $stockMapping;
        $this->generalMapping = $generalMapping;
        $this->resourceModel = $resourceModel;
        $this->additionalMapping = $additionalMapping;

        parent::__construct($staticFieldMapping);
    }

    /**
     * @inheritdoc
     */
    public function getMappingProperties()
    {
        if (null === $this->properties) {
            $allAttributesMapping = $this->getAllAttributesMappingProperties();
            $commonMappingProperties = $this->getCommonMappingProperties();
            $attributesMapping = array_merge($allAttributesMapping, $commonMappingProperties);

            $properties = $this->getCustomProperties();
            $properties['configurable_children'] = ['properties' => $attributesMapping];
            $properties = array_merge($properties, $attributesMapping);
            $properties = array_merge($properties, $this->generalMapping->getCommonProperties());

            $mapping = ['properties' => $properties];
            $mappingObject = new \Magento\Framework\DataObject();
            $mappingObject->setData($mapping);

            $this->eventManager->dispatch(
                'elasticsearch_product_mapping_properties',
                ['mapping' => $mappingObject]
            );

            $this->properties = $mappingObject->getData();
        }

        return $this->properties;
    }

    /**
     * @return array
     */
    private function getAllAttributesMappingProperties()
    {
        $attributes = $this->getAttributes();
        $allAttributesMapping = [];

        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $mapping = $this->getAttributeMapping($attribute);
            $allAttributesMapping[$attributeCode] = $mapping[$attributeCode];
        }

        $allAttributesMapping['slug'] = ['type' => FieldInterface::TYPE_KEYWORD];

        return $allAttributesMapping;
    }

    /**
     * @return array
     */
    private function getCommonMappingProperties(): array
    {
        $attributesMapping = [];
        $attributesMapping['stock']['properties'] = $this->stockMapping->get();
        $attributesMapping['media_gallery'] = [
            'properties' => [
                'type' => ['type' => FieldInterface::TYPE_TEXT],
                'image' => ['type' => FieldInterface::TYPE_TEXT],
                'lab' => ['type' => FieldInterface::TYPE_TEXT],
                'pos' => ['type' => FieldInterface::TYPE_TEXT],
                'vid' => [
                    'properties' => [
                        'url' =>  ['type' => FieldInterface::TYPE_TEXT],
                        'title' =>  ['type' => FieldInterface::TYPE_TEXT],
                        'desc' =>  ['type' => FieldInterface::TYPE_TEXT],
                        'video_id' =>  ['type' => FieldInterface::TYPE_TEXT],
                        'meta' =>  ['type' => FieldInterface::TYPE_TEXT],
                        'type' =>  ['type' => FieldInterface::TYPE_TEXT],
                    ]
                ]
            ],
        ];
        $attributesMapping['final_price'] = ['type' => FieldInterface::TYPE_DOUBLE];
        $attributesMapping['regular_price'] = ['type' => FieldInterface::TYPE_DOUBLE];
        $attributesMapping['parent_sku'] = ['type' => FieldInterface::TYPE_KEYWORD];

        return $attributesMapping;
    }

    /**
     * @return array
     */
    private function getCustomProperties(): array
    {
        $customProperties = ['attribute_set_id' => ['type' => FieldInterface::TYPE_LONG]];

        foreach ($this->additionalMapping as $propertyName => $properties) {
            if ($properties instanceof FieldMappingInterface) {
                $customProperties[$propertyName] = $properties->get();
            }
        }

        return $customProperties;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->resourceModel->execute();
    }
}
