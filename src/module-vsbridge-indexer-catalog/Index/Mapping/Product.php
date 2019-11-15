<?php

namespace Divante\VsbridgeIndexerCatalog\Index\Mapping;

use Divante\VsbridgeIndexerCore\Api\Mapping\FieldInterface;
use Divante\VsbridgeIndexerCore\Api\MappingInterface;
use Divante\VsbridgeIndexerCore\Index\Mapping\GeneralMapping;
use Divante\VsbridgeIndexerCatalog\Model\Attributes\ConfigurableAttributes;
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
     * @var array
     */
    private $properties;

    /**
     * @var LoadAttributes
     */
    private $resourceModel;

    /**
     * @var ConfigurableAttributes
     */
    private $configurableAttributes;

    /**
     * Product constructor.
     *
     * @param EventManager $eventManager
     * @param GeneralMapping $generalMapping
     * @param ConfigurableAttributes $configurableAttributes
     * @param LoadAttributes $resourceModel
     * @param array $staticFieldMapping
     */
    public function __construct(
        EventManager $eventManager,
        GeneralMapping $generalMapping,
        ConfigurableAttributes $configurableAttributes,
        LoadAttributes $resourceModel,
        array $staticFieldMapping
    ) {
        $this->eventManager = $eventManager;
        $this->generalMapping = $generalMapping;
        $this->resourceModel = $resourceModel;
        $this->configurableAttributes = $configurableAttributes;
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

            $childrenMapping = $this->getChildrenAttributeMappings($allAttributesMapping);
            $childrenMapping = array_merge($childrenMapping, $commonMappingProperties);

            $attributesMapping = array_merge($allAttributesMapping, $commonMappingProperties);

            $properties = $this->getCustomProperties();
            $properties['configurable_children'] = ['properties' => $childrenMapping];
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
     * @param array $allAttributes
     *
     * @return array
     */
    private function getChildrenAttributeMappings(array $allAttributes = [])
    {
        $list = [];

        foreach ($this->configurableAttributes->getChildrenRequiredAttributes() as $field) {
            if (isset($allAttributes[$field])) {
                $list[$field] = $allAttributes[$field];
            }
        }

        return $list;
    }

    /**
     * @return array
     */
    private function getCommonMappingProperties()
    {
        $attributesMapping = [];
        $attributesMapping['stock']['properties'] = $this->generalMapping->getStockMapping();
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

        return $attributesMapping;
    }

    /**
     * @return array
     */
    private function getCustomProperties(): array
    {
        return [
            'attribute_set_id' => ['type' => FieldInterface::TYPE_LONG],
            'bundle_options' => $this->getBundleOptionsMapping(),
            'product_links' => $this->getProductLinksMapping(),
            'configurable_options' => $this->getConfigurableOptionsMapping(),
            'category' => $this->getCategoryMapping(),
            'custom_options' => $this->getCustomOptionsMapping(),
            'tier_prices' => $this->getTierPricesMapping(),
        ];
    }

    /**
     * @return array
     */
    private function getProductLinksMapping(): array
    {
        return [
            'properties' => [
                'linked_product_type' => ['type' => FieldInterface::TYPE_TEXT],
                'linked_product_sku' => ['type' => FieldInterface::TYPE_KEYWORD],
                'sku' => ['type' => FieldInterface::TYPE_KEYWORD],
                'position' => ['type' => FieldInterface::TYPE_LONG],
            ],
        ];
    }

    /**
     * @return array
     */
    private function getConfigurableOptionsMapping(): array
    {
        return [
            'properties' => [
                'label' => ['type' => FieldInterface::TYPE_TEXT],
                'id' => ['type' => FieldInterface::TYPE_LONG],
                'product_id' => ['type' => FieldInterface::TYPE_LONG],
                'attribute_code' => ['type' => FieldInterface::TYPE_TEXT],
                'attribute_id' => ['type' => FieldInterface::TYPE_LONG],
                'position' => ['type' => FieldInterface::TYPE_LONG],
                'values' => [
                    'properties' => [
                        'value_index' => ['type' => FieldInterface::TYPE_KEYWORD],
                        'label' => ['type' => FieldInterface::TYPE_TEXT],
                        'swatch' => $this->generalMapping->getSwatchProperties(),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function getCategoryMapping(): array
    {
        return  [
            'type' => 'nested',
            'properties' => [
                'category_id' => ['type' => FieldInterface::TYPE_LONG],
                'position' => ['type' => FieldInterface::TYPE_LONG],
                'name' => [
                    'type' => FieldInterface::TYPE_TEXT,
                    'fields' => [
                        'keyword' => [
                            'type' => FieldInterface::TYPE_KEYWORD,
                            'ignore_above' => 256,
                        ]
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function getBundleOptionsMapping(): array
    {
        return [
            'properties' => [
                'option_id' => ['type' => FieldInterface::TYPE_LONG],
                'position' => ['type' => FieldInterface::TYPE_LONG],
                'title' => ['type' => FieldInterface::TYPE_TEXT],
                'sku' => ['type' => FieldInterface::TYPE_KEYWORD],
                'product_links' => [
                    'properties' => [
                        'id' => ['type' => FieldInterface::TYPE_LONG],
                        'is_default' => ['type' => FieldInterface::TYPE_BOOLEAN],
                        'qty' => ['type' => FieldInterface::TYPE_DOUBLE],
                        'can_change_quantity' => ['type' => FieldInterface::TYPE_BOOLEAN],
                        'price' => ['type' => FieldInterface::TYPE_DOUBLE],
                        'price_type' => ['type' => FieldInterface::TYPE_TEXT],
                        'position' => ['type' => FieldInterface::TYPE_LONG],
                        'sku' => ['type' => FieldInterface::TYPE_KEYWORD],
                    ],
                ],
            ]
        ];
    }

    /**
     * @return array
     */
    private function getCustomOptionsMapping(): array
    {
        return [
            'properties' => [
                'image_size_x' => ['type' => FieldInterface::TYPE_TEXT],
                'image_size_y' => ['type' => FieldInterface::TYPE_TEXT],
                'file_extension' => ['type' => FieldInterface::TYPE_TEXT],
                'is_require' => ['type' => FieldInterface::TYPE_BOOLEAN],
                'max_characters' => ['type' => FieldInterface::TYPE_TEXT],
                'option_id' => ['type' => FieldInterface::TYPE_LONG],
                'price' => ['type' => FieldInterface::TYPE_DOUBLE],
                'price_type' => ['type' => FieldInterface::TYPE_TEXT],
                'sku' => ['type' => FieldInterface::TYPE_KEYWORD],
                'sort_order' => ['type' => FieldInterface::TYPE_INTEGER],
                'title' => ['type' => FieldInterface::TYPE_TEXT],
                'type' => ['type' => FieldInterface::TYPE_TEXT],
                'values' => [
                    'properties' => [
                        'sku' => ['type' => FieldInterface::TYPE_KEYWORD],
                        'price' => ['type' => FieldInterface::TYPE_DOUBLE],
                        'title' => ['type' => FieldInterface::TYPE_TEXT],
                        'price_type' => ['type' => FieldInterface::TYPE_TEXT],
                        'sort_order' => ['type' => FieldInterface::TYPE_INTEGER],
                        'option_type_id' => ['type' => FieldInterface::TYPE_INTEGER],
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    private function getTierPricesMapping(): array
    {
        return [
            'properties' => [
                'customer_group_d' => ['type' => FieldInterface::TYPE_INTEGER],
                'qty' => ['type' => FieldInterface::TYPE_DOUBLE],
                'value' => ['type' => FieldInterface::TYPE_DOUBLE],
                'extension_attributes' => [
                    'properties' => [
                        'website_id' => ['type' => FieldInterface::TYPE_SHORT]
                    ],
                ],
            ],
        ];
    }

    /**
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->resourceModel->execute();
    }
}
