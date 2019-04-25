<?php
/**
 * @package  magento-2-1.dev
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Plugin\Indexer\Product\Save;

use Divante\VsbridgeIndexerCatalog\Model\Indexer\ProductProcessor;
use Magento\Catalog\Model\Product;

/**
 * Class UpdateProductData
 */
class UpdateProductData
{
    /**
     * @var ProductProcessor
     */
    private $productProcessor;

    /**
     * UpdateProductData constructor.
     *
     * @param ProductProcessor $processor
     */
    public function __construct(ProductProcessor $processor)
    {
        $this->productProcessor = $processor;
    }

    /**
     * Reindex data after product save/delete resource commit
     *
     * @param Product $product
     *
     * @return void
     */
    public function afterReindex(Product $product)
    {
        $this->productProcessor->reindexRow($product->getId());
    }
}
