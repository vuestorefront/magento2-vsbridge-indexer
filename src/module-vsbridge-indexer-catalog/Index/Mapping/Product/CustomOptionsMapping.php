<?php declare(strict_types=1);
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Index\Mapping\Product;

use Divante\VsbridgeIndexerCatalog\Index\Mapping\FieldMappingInterface;
use Divante\VsbridgeIndexerCore\Api\Mapping\FieldInterface;

/**
 * Class CustomOptionsMapping
 */
class CustomOptionsMapping implements FieldMappingInterface
{
    /**
     * @inheritDoc
     */
    public function get(): array
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
}
