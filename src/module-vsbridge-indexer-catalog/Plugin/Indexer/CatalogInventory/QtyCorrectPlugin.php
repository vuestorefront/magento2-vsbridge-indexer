<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Plugin\Indexer\CatalogInventory;

use Magento\CatalogInventory\Model\ResourceModel\QtyCounterInterface;

/**
 * Class QtyCorrectPlugin
 */
class QtyCorrectPlugin
{
    /**
     * @var ProductsForReindex
     */
    private $productsForReindex;

    /**
     * ProcessStockChangedPlugin constructor.
     *
     * @param ProductsForReindex $itemsForReindex
     */
    public function __construct(
        ProductsForReindex $itemsForReindex
    ) {
        $this->productsForReindex = $itemsForReindex;
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
}
