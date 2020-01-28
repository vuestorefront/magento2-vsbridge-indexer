<?php declare(strict_types=1);
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product\Configurable;

/**
 * Class PrepareConfigurableProduct
 */
class PrepareConfigurableProduct
{
    /**
     * @param array $productDTO
     *
     * @return array
     */
    public function execute(array $productDTO): array
    {
        $configurableChildren = $productDTO['configurable_children'];
        $areChildInStock = 0;
        $finalPrice = $childPrice = [];
        $hasPrice = $this->hasPrice($productDTO);

        foreach ($configurableChildren as $child) {
            if (!empty($child['stock']['is_in_stock'])) {
                $areChildInStock = 1;
            }

            if (isset($child['price'])) {
                $childPrice[] = $child['price'];
                $finalPrice[] = $child['final_price'] ?? $child['final_price'] ?? $child['price'];
            }
        }

        if (!empty($childPrice)) {
            $finalPrice = min($finalPrice);

            if (!$hasPrice) {
                $minPrice = min($childPrice);
                $productDTO['price'] = $minPrice;
                $productDTO['regular_price'] = $minPrice;
                $productDTO['final_price'] = $finalPrice;
            } else {
                $productDTO['final_price'] = min($finalPrice, $productDTO['final_price']);
            }
        }

        if (!empty($productDTO['stock']['is_in_stock']) || !$areChildInStock) {
            $productDTO['stock']['is_in_stock'] = false;
            $productDTO['stock']['stock_status'] = 0;
        }

        return $productDTO;
    }

    /**
     * @param array $product
     *
     * @return bool
     */
    private function hasPrice(array $product): bool
    {
        $priceFields = [
            'price',
            'final_price',
        ];

        foreach ($priceFields as $field) {
            if (!isset($product[$field])) {
                return false;
            }

            if (0 === (int) $product[$field]) {
                return false;
            }
        }

        return true;
    }
}
