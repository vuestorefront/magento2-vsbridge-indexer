<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Index;

use Divante\VsbridgeIndexerCore\Api\ConvertDataTypesInterface;
use Divante\VsbridgeIndexerCore\Api\Index\TypeInterface;
use Divante\VsbridgeIndexerCore\Api\Mapping\FieldInterface;

/**
 * Class ConvertDataTypes
 */
class ConvertDataTypes implements ConvertDataTypesInterface
{
    /**
     * @var array
     */
    private $castMapping = [
        FieldInterface::TYPE_TEXT => 'string',
        FieldInterface::TYPE_LONG => 'int',
        FieldInterface::TYPE_INTEGER => 'int',
        FieldInterface::TYPE_BOOLEAN => 'bool',
        FieldInterface::TYPE_DOUBLE => 'double',
    ];

    /**
     * @param TypeInterface $type
     * @param array $docs
     *
     * @return array
     */
    public function castFieldsUsingMapping(TypeInterface $type, array $docs)
    {
        $mapping = $type->getMapping();

        if ($mapping) {
            $mappingProperties = $mapping->getMappingProperties()['properties'];

            foreach ($docs as $docId => $indexData) {
                unset($indexData['entity_id']);
                unset($indexData['row_id']);

                $indexData = $this->convert($indexData, $mappingProperties);

                if (isset($indexData['configurable_children'])) {
                    foreach ($indexData['configurable_children'] as $key => $child) {
                        $child = $this->convert($child, $mappingProperties);
                        $indexData['configurable_children'][$key] = $child;
                    }
                }

                if (isset($indexData['children_data'])) {
                    foreach ($indexData['children_data'] as $index => $subCategory) {
                        $subCategory = $this->convertChildrenData($subCategory, $mappingProperties);
                        $indexData['children_data'][$index] = $subCategory;
                    }
                }

                $docs[$docId] = $indexData;
            }
        }

        return $docs;
    }

    /**
     * @param array $indexData
     * @param array $mappingProperties
     *
     * @return array
     */
    private function convert(array $indexData, array $mappingProperties)
    {
        foreach ($mappingProperties as $fieldKey => $options) {
            if (isset($options['type'])) {
                $type = $this->getCastType($options['type']);

                if ($type && isset($indexData[$fieldKey]) && (null !== $indexData[$fieldKey])) {
                    if (is_array($indexData[$fieldKey])) {
                        foreach ($indexData[$fieldKey] as $value) {
                            settype($value, $type);
                        }
                    } else {
                        settype($indexData[$fieldKey], $type);
                    }
                }
            }
        }

        return $indexData;
    }

    /**
     * @param array $category
     * @param  array $mappingProperties
     *
     * @return array
     */
    private function convertChildrenData(array $category, array $mappingProperties)
    {
        $childrenData = $category['children_data'];

        foreach ($childrenData as $index => $subCategory) {
            $subCategory = $this->convert($subCategory, $mappingProperties);
            $subCategory = $this->convertChildrenData($subCategory, $mappingProperties);
            $childrenData[$index] = $subCategory;
        }

        $category['children_data'] = $childrenData;

        return $category;
    }

    /**
     * @param string $esFieldType
     *
     * @return string|null
     */
    private function getCastType($esFieldType)
    {
        if (isset($this->castMapping[$esFieldType])) {
            return $this->castMapping[$esFieldType];
        }

        return null;
    }
}
