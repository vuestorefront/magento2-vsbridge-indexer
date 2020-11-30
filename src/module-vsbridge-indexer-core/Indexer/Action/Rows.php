<?php


namespace Divante\VsbridgeIndexerCore\Indexer\Action;

use Divante\VsbridgeIndexerCore\Cache\Processor;
use Divante\VsbridgeIndexerCore\Indexer\GenericIndexerHandlerFactory;
use Divante\VsbridgeIndexerCore\Indexer\RebuildActionPool;
use Divante\VsbridgeIndexerCore\Indexer\StoreManager;

/**
 * Rows reindex action
 */
class Rows extends AbstractAction
{
    /**
     * @var Processor
     */
    private $cacheProcessor;

    /**
     * Rows constructor.
     *
     * @param RebuildActionPool $actionPool
     * @param GenericIndexerHandlerFactory $indexerHandlerFactory
     * @param StoreManager $storeManager
     * @param Processor $cacheProcessor
     * @param string $typeName
     */
    public function __construct(
        RebuildActionPool $actionPool,
        GenericIndexerHandlerFactory $indexerHandlerFactory,
        StoreManager $storeManager,
        Processor $cacheProcessor,
        string $typeName
    ) {
        parent::__construct($actionPool, $indexerHandlerFactory, $storeManager, $typeName);
        $this->cacheProcessor = $cacheProcessor;
    }

    /**
     * Execute rows reindex
     *
     * @param array $ids
     *
     * @return void
     */
    public function execute(array $ids)
    {
        $stores = $this->getStores();

        foreach ($stores as $store) {
            $this->getIndexerHandler()->saveIndex($this->rebuild((int) $store->getId(), $ids), $store);
            $this->getIndexerHandler()->cleanUpByTransactionKey($store, $ids);
            $this->cacheProcessor->cleanCacheByDocIds($store->getId(), $this->getIndexerHandler()->getTypeName(), $ids);
        }
    }
}
