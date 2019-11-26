<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Index\Mapping;

use Divante\VsbridgeIndexerCatalog\Model\Attributes\CategoryChildAttributes;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category\LoadAttributes;
use Divante\VsbridgeIndexerCore\Api\MappingInterface;
use Divante\VsbridgeIndexerCore\Api\Mapping\FieldInterface;
use Divante\VsbridgeIndexerCore\Index\Mapping\GeneralMapping;
use Magento\Framework\Event\ManagerInterface as EventManager;

/**
 * Class Category
 */
class Category extends AbstractMapping implements MappingInterface
{
    /**
     * @var array
     */
    private $omitAttributes = [
        'children',
        'all_children',
    ];

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var GeneralMapping
     */
    private $generalMapping;

    /**
     * @var LoadAttributes
     */
    private $resourceModel;

    /**
     * @var array
     */
    private $properties;

    /**
     * @var CategoryChildAttributes
     */
    private $childAttributes;

    /**
     * Category constructor.
     *
     * @param EventManager $eventManager
     * @param GeneralMapping $generalMapping
     * @param CategoryChildAttributes $categoryChildAttributes
     * @param LoadAttributes $resourceModel
     * @param array $staticFieldMapping
     */
    public function __construct(
        EventManager $eventManager,
        GeneralMapping $generalMapping,
        CategoryChildAttributes $categoryChildAttributes,
        LoadAttributes $resourceModel,
        array $staticFieldMapping
    ) {
        $this->eventManager = $eventManager;
        $this->generalMapping = $generalMapping;
        $this->resourceModel = $resourceModel;
        $this->childAttributes = $categoryChildAttributes;
        parent::__construct($staticFieldMapping);
    }

    /**
     * @inheritdoc
     */
    public function getMappingProperties()
    {
        if (null === $this->properties) {
            $attributesMapping = $this->getAllAttributesMapping();

            $properties = $this->generalMapping->getCommonProperties();
            $properties['children_count'] = ['type' => FieldInterface::TYPE_INTEGER];
            $properties['product_count'] = ['type' => FieldInterface::TYPE_INTEGER];

            $childMapping = $this->getChildrenDataMapping($attributesMapping, $properties);
            $properties['children_data'] = ['properties' => $childMapping];
            $properties = array_merge($properties, $attributesMapping);

            /*TODO grid_per_page -> not implemented yet*/
            $properties['grid_per_page'] = ['type' => FieldInterface::TYPE_INTEGER];
            $mapping = ['properties' => $properties];
            $mappingObject = new \Magento\Framework\DataObject();
            $mappingObject->setData($mapping);

            $this->eventManager->dispatch(
                'elasticsearch_category_mapping_properties',
                ['mapping' => $mappingObject]
            );

            $this->properties = $mappingObject->getData();
        }

        return $this->properties;
    }

    /**
     * @return array
     */
    private function getAllAttributesMapping()
    {
        $attributes = $this->getAttributes();
        $allAttributesMapping = [];

        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            if (in_array($attributeCode, $this->omitAttributes)) {
                continue;
            }

            $mapping = $this->getAttributeMapping($attribute);
            $allAttributesMapping[$attributeCode] = $mapping[$attributeCode];
        }

        $allAttributesMapping['slug'] = ['type' => FieldInterface::TYPE_KEYWORD];

        return $allAttributesMapping;
    }

    /**
     * @param array $allAttributesMapping
     * @param array $commonProperties
     *
     * @return array
     */
    private function getChildrenDataMapping(array $allAttributesMapping, array $commonProperties)
    {
        $childMapping = [];

        foreach ($this->childAttributes->getRequiredFields() as $field) {
            if (isset($allAttributesMapping[$field])) {
                $childMapping[$field] = $allAttributesMapping[$field];
            }
        }

        $childMapping = array_merge($commonProperties, $childMapping);
        unset($childMapping['created_at'], $childMapping['updated_at']);

        return $childMapping;
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
