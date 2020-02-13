<?php declare(strict_types=1);
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Index\Mapping\Attribute;

use Divante\VsbridgeIndexerCatalog\Index\Mapping\FieldMappingInterface;
use Divante\VsbridgeIndexerCore\Api\Mapping\FieldInterface;

/**
 * Class SwatchMapping
 */
class SwatchMapping implements FieldMappingInterface
{
    /**
     * Retrieve Swatch Mapping
     *
     * @return array
     */
    public function get(): array
    {
        return [
            'properties' => [
                'value' => ['type' => FieldInterface::TYPE_TEXT],
                'type' => ['type' => FieldInterface::TYPE_SHORT], // to make it compatible with other fields
            ]
        ];
    }
}
