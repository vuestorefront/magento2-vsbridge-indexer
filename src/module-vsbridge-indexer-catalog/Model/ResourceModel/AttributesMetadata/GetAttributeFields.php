<?php

declare(strict_types = 1);

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\AttributesMetadata;

use Divante\VsbridgeIndexerCore\Api\ConvertValueInterface;
use Divante\VsbridgeIndexerCatalog\Index\Mapping\Attribute as AttributeMapping;

/**
 * Class GetAttributeFields
 */
class GetAttributeFields
{
    /**
     * @var array
     */
    private $requiredColumns = [
        'is_visible_on_front',
        'is_visible',
        'attribute_id',
        'entity_type_id',
        'frontend_input',
        'attribute_id',
        'frontend_input',
        'is_user_defined',
        'is_comparable',
        'source_model',
        'attribute_code',
    ];

    /**
     * @var ConvertValueInterface
     */
    private $convertValue;

    /**
     * @var AttributeMapping
     */
    private $attributeMapping;

    /**
     * GetAttributeFields constructor.
     *
     * @param AttributeMapping $attributeMapping
     * @param ConvertValueInterface $convertValue
     */
    public function __construct(
        AttributeMapping $attributeMapping,
        ConvertValueInterface $convertValue
    ) {
        $this->attributeMapping = $attributeMapping;
        $this->convertValue = $convertValue;
    }

    /**
     * @param array $row
     *
     * @return array
     */
    public function execute(array $row)
    {
        $attribute['id'] = (int)$row['attribute_id'];
        $attribute['default_frontend_label'] = $row['frontend_label'];

        foreach ($this->requiredColumns as $column) {
            $attribute[$column] = $this->convertValue->execute($this->attributeMapping, $column, $row[$column]);
        }

        return $attribute;
    }
}

