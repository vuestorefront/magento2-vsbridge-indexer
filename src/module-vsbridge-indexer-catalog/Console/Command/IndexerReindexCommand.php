<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Console\Command;

use Divante\VsbridgeIndexerCatalog\Model\Indexer\ProductCategoryProcessor;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class IndexerReindexCommand
 */
class IndexerReindexCommand extends \Magento\Indexer\Console\Command\IndexerReindexCommand
{
    /**
     * @inheritdoc
     */
    protected function getIndexers(InputInterface $input)
    {
        $indexers = parent::getIndexers($input);
        unset($indexers[ProductCategoryProcessor::INDEXER_ID]);

        return $indexers;
    }
}
