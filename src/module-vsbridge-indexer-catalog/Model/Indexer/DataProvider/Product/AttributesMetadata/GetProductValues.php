<?php

declare(strict_types=1);

/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product\AttributesMetadata;

/**
 * Class GetProductValues
 */
class GetProductValues
{
    /**
     * @param array $productDTO
     * @param string $attributeCode
     *
     * @return array
     */
    public function execute(array $productDTO, string $attributeCode)
    {
        $attributeValue = isset($productDTO[$attributeCode]) ? $productDTO[$attributeCode] : '';

        if (!is_array($attributeValue)) {
            $attributeValue = [$attributeValue];
        }

        $options = $this->getOptionsForChildren($productDTO, $attributeCode);
        $options = array_merge($options, $attributeValue);

        if (!empty($options)) {
            $options = array_unique($options);
        }

        return $options;
    }

    /**
     * @param array $productDTO
     * @param string $attributeCode
     *
     * @return array
     */
    private function getOptionsForChildren(array $productDTO, $attributeCode): array
    {
        if (!isset($productDTO['configurable_children'])) {
            return [];
        }

        $options = [];

        foreach ($productDTO['configurable_children'] as $child) {
            if (isset($child[$attributeCode])) {
                if (is_array($child[$attributeCode])) {
                    $options = array_merge($options, $child[$attributeCode]);
                } else {
                    $options[] = $child[$attributeCode];
                }
            }
        }

        return $options;
    }
}
