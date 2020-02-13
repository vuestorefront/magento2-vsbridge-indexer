<?php declare(strict_types=1);
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Index\Mapping\Product;

use Divante\VsbridgeIndexerCatalog\Index\Mapping\Attribute\SwatchMapping;
use Divante\VsbridgeIndexerCatalog\Index\Mapping\FieldMappingInterface;
use Divante\VsbridgeIndexerCore\Api\Mapping\FieldInterface;

/**
 * Class ConfigurableOptionsMapping
 */
class ConfigurableOptionsMapping implements FieldMappingInterface
{
    /**
     * @var SwatchMapping
     */
    private $swatchMapping;

    /**
     * ConfigurableOptionsMapping constructor.
     *
     * @param SwatchMapping $swatchMapping
     */
    public function __construct(SwatchMapping $swatchMapping)
    {
        $this->swatchMapping = $swatchMapping;
    }

    /**
     * @inheritdoc
     */
    public function get(): array
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
                        'swatch' => $this->swatchMapping->get(),
                    ],
                ],
            ],
        ];
    }
}
