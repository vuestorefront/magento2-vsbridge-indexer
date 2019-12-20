<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer;

use Magento\Framework\Indexer\{Config\DependencyInfoProviderInterface, IndexerRegistry};

/**
 * Class ProductProcessor
 */
class ProductProcessor extends \Magento\Framework\Indexer\AbstractProcessor
{
    /**
     * Indexer ID
     */
    const INDEXER_ID = 'vsbridge_product_indexer';

    /**
     * @var DependencyInfoProviderInterface
     */
    private $dependencyInfoProvider;

    /**
     * ProductProcessor constructor.
     *
     * @param DependencyInfoProviderInterface $dependencyInfoProvider
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        DependencyInfoProviderInterface $dependencyInfoProvider,
        IndexerRegistry $indexerRegistry
    ) {
        parent::__construct($indexerRegistry);
        $this->dependencyInfoProvider = $dependencyInfoProvider;
    }

    /**
     * Mark Vsbridge Product indexer as invalid
     *
     * @return void
     */
    public function markIndexerAsInvalid()
    {
        $this->getIndexer()->invalidate();
    }

    /**
     * Run Row reindex
     *
     * @param int $id
     * @param bool $forceReindex
     * @return void
     */
    public function reindexRow($id, $forceReindex = false)
    {
        if ($this->hasToReindex()) {
            parent::reindexRow($id, $forceReindex);
        }
    }

    /**
     * @param int[] $ids
     * @param bool $forceReindex
     */
    public function reindexList($ids, $forceReindex = false)
    {
        if ($this->hasToReindex()) {
            parent::reindexList($ids, $forceReindex);
        }
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function hasToReindex(): bool
    {
        $hasToRun = true;
        $dependentIndexerIds = $this->dependencyInfoProvider->getIndexerIdsToRunBefore($this->getIndexerId());

        foreach ($dependentIndexerIds as $indexerId) {
            $dependentIndexer = $this->indexerRegistry->get($indexerId);

            if (!$dependentIndexer->isScheduled()) {
                $hasToRun = false;
                break;
            }
        }

        return $hasToRun;
    }
}
