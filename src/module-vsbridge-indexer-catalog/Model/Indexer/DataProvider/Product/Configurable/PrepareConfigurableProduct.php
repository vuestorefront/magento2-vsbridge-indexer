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
        $specialPrice = $finalPrice = $childPrice = [];

        foreach ($configurableChildren as $child) {
            if (!empty($child['stock']['is_in_stock'])) {
                $areChildInStock = 1;
            }

            if (isset($child['special_price'])) {
                $specialPrice[] = $child['special_price'];
            }

            if (isset($child['price'])) {
                $childPrice[] = $child['price'];
                $finalPrice[] = $child['final_price'] ?? $child['price'];
            }
        }

        $productDTO['final_price'] = !empty($finalPrice) ? min($finalPrice): null;
        $productDTO['special_price'] = !empty($specialPrice) ? min($specialPrice) : null;
        $productDTO['price'] = !empty($childPrice) ? min($childPrice): null;
        $productDTO['regular_price'] = $productDTO['price'];


        if (empty($productDTO['stock']['is_in_stock']) || !$areChildInStock) {
            $productDTO['stock']['is_in_stock'] = false;
            $productDTO['stock']['stock_status'] = 0;
        }

        return $productDTO;
    }
}
