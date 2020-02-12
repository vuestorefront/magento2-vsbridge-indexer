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
 * Class BundleOptionsMapping
 */
class BundleOptionsMapping implements FieldMappingInterface
{
    /**
     * @inheritdoc
     */
    public function get(): array
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
}
