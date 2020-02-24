<?php

declare(strict_types=1);

/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Index\Mapping\Product;

use Divante\VsbridgeIndexerCatalog\Index\Mapping\Attribute\OptionMapping;
use Divante\VsbridgeIndexerCatalog\Index\Mapping\FieldMappingInterface;
use Divante\VsbridgeIndexerCore\Api\Mapping\FieldInterface;

/**
 * Class AttributesMetaMapping
 */
class AttributesMetaMapping implements FieldMappingInterface
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
     * @return array
     */
    public function get(): array
    {
        return [
            'properties' => [
                'id' => ['type' => FieldInterface::TYPE_INTEGER],
                'attribute_id' => ['type' => FieldInterface::TYPE_INTEGER],
                'default_frontend_label' => ['type' => FieldInterface::TYPE_TEXT],
                'is_visible_on_front' => ['type' => FieldInterface::TYPE_BOOLEAN],
                'is_visible'  => ['type' => FieldInterface::TYPE_BOOLEAN],
                'frontend_input' => ['type' => FieldInterface::TYPE_TEXT],
                'is_user_defined' => ['type' => FieldInterface::TYPE_BOOLEAN],
                'is_comparable' => ['type' => FieldInterface::TYPE_BOOLEAN],
                'attribute_code' => ['type' => FieldInterface::TYPE_TEXT],
                'options' => $this->optionMapping->get(),
            ],
        ];
    }
}
