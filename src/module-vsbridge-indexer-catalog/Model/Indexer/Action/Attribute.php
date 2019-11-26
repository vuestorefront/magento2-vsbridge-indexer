<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\Action;

use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Attribute as ResourceModel;
use Divante\VsbridgeIndexerCatalog\Index\Mapping\Attribute as AttributeMapping;
use Divante\VsbridgeIndexerCore\Api\ConvertValueInterface;

/**
 * Class Attribute
 */
class Attribute
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
     * @param array $attributeIds
     *
     * @return \Traversable
     */
    public function rebuild(array $attributeIds = [])
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
        foreach ($attributeData as $key => &$value) {
            $this->convertValue->execute($this->attributeMapping, $key, $value);
        }

        return $attributeData;
    }
}
