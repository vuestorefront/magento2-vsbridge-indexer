<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Plugin\Indexer\CatalogInventory;

/**
 * Class ProductsForReindex
 */
class ProductsForReindex
{
    /**
     * @var array
     */
    protected $productsForReindex = [];

    /**
     * @param array $items
     * @return void
     */
    public function setProducts(array $items)
    {
        $this->productsForReindex = $items;
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->productsForReindex;
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->productsForReindex = [];
    }
}
