<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Plugin\Indexer\CatalogInventory;

use Divante\VsbridgeIndexerCatalog\Model\Indexer\ProductProcessor;
use Magento\CatalogInventory\Model\ResourceModel\QtyCounterInterface;
use Magento\CatalogInventory\Observer\ReindexQuoteInventoryObserver;

/**
 * Class ProcessStockChangedPlugin
 */
class ProcessStockChangedPlugin
{
    /**
     * @var ProductsForReindex
     */
    private $productsForReindex;

    /**
     * @var ProductProcessor
     */
    private $productProcessor;

    /**
     * ProcessStockChangedPlugin constructor.
     *
     * @param ProductsForReindex $itemsForReindex
     * @param ProductProcessor $processor
     */
    public function __construct(
        ProductsForReindex $itemsForReindex,
        ProductProcessor $processor
    ) {
        $this->productsForReindex = $itemsForReindex;
        $this->productProcessor = $processor;
    }

    /**
     * @param QtyCounterInterface $subject
     * @param callable $proceed
     * @param array $items
     * @param int $websiteId
     * @param string $operator
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCorrectItemsQty(
        QtyCounterInterface $subject,
        callable $proceed,
        array $items,
        $websiteId,
        $operator
    ) {
        if (!empty($items)) {
            $productIds = array_keys($items);
            $this->productsForReindex->setProducts($productIds);
        }

        $proceed($items, $websiteId, $operator);
    }

    /**
     * @param ReindexQuoteInventoryObserver $subject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @return void
     */
    public function afterExecute(ReindexQuoteInventoryObserver $subject)
    {
        $products = $this->productsForReindex->getProducts();

        if (!empty($products)) {
            $this->productProcessor->reindexList($products);
            $this->productsForReindex->clear();
        }
    }
}
