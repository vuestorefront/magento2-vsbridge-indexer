<?php
/**
 * @package   Divante\VsbridgeIndexerCms
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCms\Model\Indexer;

use Divante\VsbridgeIndexerCore\Indexer\StoreManager;
use Divante\VsbridgeIndexerCms\Model\Indexer\Action\CmsPage as CmsPageAction;
use Divante\VsbridgeIndexerCore\Indexer\GenericIndexerHandler;
use Divante\VsbridgeIndexerCore\Cache\Processor as CacheProcessor;

/**
 * Class CmsPage
 */
class CmsPage implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var GenericIndexerHandler
     */
    private $indexHandler;

    /**
     * @var CmsPageAction
     */
    private $cmsPageAction;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var CacheProcessor
     */
    private $cacheProcessor;

    /**
     * CmsBlock constructor.
     *
     * @param CacheProcessor $cacheProcessor
     * @param GenericIndexerHandler $indexerHandler
     * @param StoreManager $storeManager
     * @param CmsPageAction $action
     */
    public function __construct(
        CacheProcessor $cacheProcessor,
        GenericIndexerHandler $indexerHandler,
        StoreManager $storeManager,
        CmsPageAction $action
    ) {
        $this->indexHandler = $indexerHandler;
        $this->storeManager = $storeManager;
        $this->cmsPageAction = $action;
        $this->cacheProcessor = $cacheProcessor;
    }

    /**
     * @inheritdoc
     */
    public function execute($ids)
    {
        $stores = $this->storeManager->getStores();

        foreach ($stores as $store) {
            $this->indexHandler->saveIndex($this->cmsPageAction->rebuild($store->getId(), $ids), $store);
            $this->indexHandler->cleanUpByTransactionKey($store, $ids);
            $this->cacheProcessor->cleanCacheByTags($store->getId(), ['cmsPage']);
        }
    }

    /**
     * @inheritdoc
     */
    public function executeFull()
    {
        $stores = $this->storeManager->getStores();

        foreach ($stores as $store) {
            $this->indexHandler->createIndex($store);
            $this->indexHandler->saveIndex($this->cmsPageAction->rebuild($store->getId()), $store);
            $this->cacheProcessor->cleanCacheByTags($store->getId(), ['cmsPage']);
        }
    }

    /**
     * @inheritdoc
     */
    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    /**
     * @inheritdoc
     */
    public function executeRow($id)
    {
        $this->execute([$id]);
    }
}
