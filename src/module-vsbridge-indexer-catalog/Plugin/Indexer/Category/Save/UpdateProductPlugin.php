<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Plugin\Indexer\Category\Save;

use Divante\VsbridgeIndexerCatalog\Model\Indexer\ProductCategoryProcessor;
use Divante\VsbridgeIndexerCatalog\Model\Indexer\ProductProcessor;

/**
 * Class UpdateProductPlugin
 */
class UpdateProductPlugin
{
    /**
     * @var ProductCategoryProcessor
     */
    private $productCategoryProcessor;

    /**
     * @var ProductProcessor
     */
    private $productProcessor;

    /**
     * UpdateProduct constructor.
     *
     * @param ProductProcessor $productProcessor
     * @param ProductCategoryProcessor $processor
     */
    public function __construct(
        ProductProcessor $productProcessor,
        ProductCategoryProcessor $processor
    ) {
        $this->productProcessor = $productProcessor;
        $this->productCategoryProcessor = $processor;
    }

    /**
     * Update product category data in ES after changing category products
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Model\Category
     */
    public function afterSave(\Magento\Catalog\Model\Category $category)
    {
        $isChangedProductList = $category->getData('is_changed_product_list');

        if (!$isChangedProductList) {
            return $category;
        }

        if (!$this->productProcessor->isIndexerScheduled() && !$this->productCategoryProcessor->isIndexerScheduled()) {
            $this->productCategoryProcessor->reindexList($category->getAffectedProductIds());
        }

        return $category;
    }
}
