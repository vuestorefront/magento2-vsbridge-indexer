<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer;

/**
 * Class ProductCategoryProcessor
 */
class ProductCategoryProcessor extends \Magento\Framework\Indexer\AbstractProcessor
{
    /**
     * Indexer ID
     */
    const INDEXER_ID = 'vsbridge_product_category';

    /**
     * Mark Vsbridge Product indexer as invalid
     *
     * @return void
     */
    public function markIndexerAsInvalid()
    {
        $this->getIndexer()->invalidate();
    }
}
