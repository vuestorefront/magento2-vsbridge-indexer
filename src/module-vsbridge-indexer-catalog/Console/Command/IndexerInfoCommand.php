<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.com>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Console\Command;

use Divante\VsbridgeIndexerCatalog\Model\Indexer\ProductCategoryProcessor;

/**
 * @inheritDoc
 */
class IndexerInfoCommand extends \Magento\Indexer\Console\Command\IndexerInfoCommand
{
    /**
     * @inheritdoc
     */
    protected function getAllIndexers()
    {
        $indexers = parent::getAllIndexers();
        unset($indexers[ProductCategoryProcessor::INDEXER_ID]);

        return $indexers;
    }
}
