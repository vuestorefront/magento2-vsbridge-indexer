<?php
/**
 * Copyright Divante Sp. z o.o.
 * See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerAgreement\Index\Mapping;

use Divante\VsbridgeIndexerCore\Api\Mapping\FieldInterface;
use Divante\VsbridgeIndexerCore\Api\MappingInterface;

class Agreement implements MappingInterface
{
    /**
     * @return array|mixed|null
     */
    public function getMappingProperties()
    {
        $properties = [
            'id' => ['type' => FieldInterface::TYPE_LONG],
            'content' => ['type' => FieldInterface::TYPE_TEXT],
            'active' => ['type' => FieldInterface::TYPE_BOOLEAN],
            'title' => ['type' => FieldInterface::TYPE_TEXT],
            'content_height' => ['type' => FieldInterface::TYPE_TEXT],
            'checkbox_text' => ['type' => FieldInterface::TYPE_TEXT],
            'is_html' => ['type' => FieldInterface::TYPE_BOOLEAN],
            'mode' => ['type' => FieldInterface::TYPE_INTEGER],
        ];

        return ['properties' => $properties];
    }
}
