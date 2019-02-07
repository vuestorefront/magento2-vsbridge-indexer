<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer;

use Magento\Framework\Indexer\AbstractProcessor;

/**
 * Class ProductProcessor
 */
class CategoryProcessor extends AbstractProcessor
{
    /**
     * Indexer ID
     */
    const INDEXER_ID = 'vsbridge_category_indexer';
}
