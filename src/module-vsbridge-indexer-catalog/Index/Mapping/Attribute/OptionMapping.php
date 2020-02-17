<?php

declare(strict_types=1);

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
 * Class OptionMapping
 */
class OptionMapping implements FieldMappingInterface
{
    /**
     * @var SwatchMapping
     */
    private $swatchMapping;

    /**
     * Attribute constructor.
     *
     * @param SwatchMapping $generalMapping
     */
    public function __construct(SwatchMapping $generalMapping)
    {
        $this->swatchMapping = $generalMapping;
    }

    /**
     * @inheritdoc
     */
    public function get(): array
    {
        return [
            'properties' => [
                'value' => ['type' => FieldInterface::TYPE_TEXT],
                'label' => ['type' => FieldInterface::TYPE_TEXT],
                'sort_order' => ['type' => FieldInterface::TYPE_INTEGER],
                'swatch' => $this->swatchMapping->get(),
            ]
        ];
    }
}
