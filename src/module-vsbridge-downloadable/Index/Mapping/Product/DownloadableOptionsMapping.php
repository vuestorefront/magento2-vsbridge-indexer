<?php
/**
 * Copyright Divante Sp. z o.o.
 * See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types=1);

namespace Divante\VsbridgeDownloadable\Index\Mapping\Product;

use Divante\VsbridgeIndexerCatalog\Index\Mapping\FieldMappingInterface;
use Divante\VsbridgeIndexerCore\Api\Mapping\FieldInterface;

/**
 * Class DownloadableOptionsMapping
 */
class DownloadableOptionsMapping implements FieldMappingInterface
{
    /**
     * @return array
     */
    public function get(): array
    {
        return [
            'properties' => [
                'extension_attributes' => [
                    'properties' => [
                        'downloadable_product_links' => [
                            'properties' => [
                                'id' => ['type' => FieldInterface::TYPE_LONG],
                                'product_id' => ['type' => FieldInterface::TYPE_LONG],
                                'sort_order' => ['type' => FieldInterface::TYPE_INTEGER],
                                'number_of_downloads' => ['type' => FieldInterface::TYPE_LONG],
                                'is_shareable' => ['type' => FieldInterface::TYPE_INTEGER],
                                'link_url' => ['type' => FieldInterface::TYPE_TEXT],
                                'link_file' => ['type' => FieldInterface::TYPE_TEXT],
                                'link_type' => ['type' => FieldInterface::TYPE_KEYWORD],
                                'sample_url' => ['type' => FieldInterface::TYPE_TEXT],
                                'sample_file' => ['type' => FieldInterface::TYPE_TEXT],
                                'sample_type' => ['type' => FieldInterface::TYPE_KEYWORD],
                                'title' => ['type' => FieldInterface::TYPE_TEXT],
                                'price' => ['type' => FieldInterface::TYPE_DOUBLE],
                            ],
                        ],
                        'downloadable_product_samples' => [
                            'properties' => [
                                'id' => ['type' => FieldInterface::TYPE_LONG],
                                'product_id' => ['type' => FieldInterface::TYPE_LONG],
                                'sample_url' => ['type' => FieldInterface::TYPE_TEXT],
                                'sample_file' => ['type' => FieldInterface::TYPE_TEXT],
                                'sample_type' => ['type' => FieldInterface::TYPE_KEYWORD],
                                'sort_order' => ['type' => FieldInterface::TYPE_INTEGER],
                                'title' => ['type' => FieldInterface::TYPE_TEXT],
                            ],
                        ]
                    ],
                ],
            ],
        ];
    }
}
