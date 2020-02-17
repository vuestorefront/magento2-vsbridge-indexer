<?php

namespace Divante\VsbridgeIndexerCatalog\Index\Mapping;

use Divante\VsbridgeIndexerCatalog\Index\Mapping\Attribute\OptionMapping;
use Divante\VsbridgeIndexerCore\Api\MappingInterface;
use Divante\VsbridgeIndexerCore\Api\Mapping\FieldInterface;

/**
 * Class Attribute
 */
class Attribute implements MappingInterface
{
    /**
     * @var OptionMapping
     */
    private $optionMapping;

    /**
     * Attribute constructor.
     *
     * @param OptionMapping $optionMapping
     */
    public function __construct(OptionMapping $optionMapping)
    {
        $this->optionMapping = $optionMapping;
    }

    /**
     * @var array
     */
    private $booleanProperties = [
        'is_required',
        'is_user_defined',
        'is_unique',
        'is_global',
        'is_visible',
        'is_searchable',
        'is_comparable',
        'is_visible_on_front',
        'is_html_allowed_on_front',
        'is_used_for_price_rules',
        'is_filterable_in_search',
        'used_in_product_listing',
        'used_for_sort_by',
        'is_configurable',
        'is_visible_in_advanced_search',
        'is_wysiwyg_enabled',
        'is_used_for_promo_rules',
    ];

    /**
     * @var array
     */
    private $longProperties = [
        'attribute_id',
        'id',
        'search_weight',
        'entity_type_id',
        'position',
    ];

    /**
     * @var array
     */
    private $integerProperties = [
        'is_filterable',
    ];

    /**
     * @var array
     */
    private $stringProperties  = [
        'attribute_code',
        'swatch_input_type',
        'attribute_model',
        'backend_model',
        'backend_table',
        'apply_to',
        'frontend_model',
        'frontend_input',
        'frontend_label',
        'frontend_class',
        'source_model',
        'default_value',
        'frontend_input_renderer',
    ];

    /**
     * @inheritdoc
     */
    public function getMappingProperties()
    {
        $properties = [];

        foreach ($this->booleanProperties as $property) {
            $properties[$property] = ['type' => FieldInterface::TYPE_BOOLEAN];
        }

        foreach ($this->longProperties as $property) {
            $properties[$property] = ['type' => FieldInterface::TYPE_LONG];
        }

        foreach ($this->integerProperties as $property) {
            $properties[$property] = ['type' => FieldInterface::TYPE_INTEGER];
        }

        foreach ($this->stringProperties as $property) {
            $properties[$property] = ['type' => FieldInterface::TYPE_TEXT];
        }

        $properties['options'] = $this->optionMapping->get();

        return ['properties' => $properties];
    }
}
