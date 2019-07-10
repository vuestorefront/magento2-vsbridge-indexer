<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Plugin\Indexer\CatalogInventory;

use Divante\VsbridgeIndexerCatalog\Model\Indexer\ProductProcessor;
use Magento\CatalogInventory\Observer\RevertQuoteInventoryObserver;

/**
 * Class RevertQuoteInventoryObserverPlugin
 */
class RevertQuoteInventoryObserverPlugin
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
     * @param RevertQuoteInventoryObserver $subject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @return void
     */
    public function afterExecute(RevertQuoteInventoryObserver $subject)
    {
        $products = $this->productsForReindex->getProducts();

        if (!empty($products)) {
            $this->productProcessor->reindexList($products);
            $this->productsForReindex->clear();
        }
    }
}
