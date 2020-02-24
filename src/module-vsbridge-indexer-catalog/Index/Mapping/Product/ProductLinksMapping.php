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
 * Class ProductLinksMapping
 */
class ProductLinksMapping implements FieldMappingInterface
{
    /**
     * @inheritdoc
     */
    public function get(): array
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
}
