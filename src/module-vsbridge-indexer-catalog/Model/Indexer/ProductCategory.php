<?php
/**
 * @package   magento-2-1.dev
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer;

use Divante\VsbridgeIndexerCatalog\Model\Indexer\Action\Product as ProductAction;
use Divante\VsbridgeIndexerCore\Indexer\GenericIndexerHandler;
use Divante\VsbridgeIndexerCore\Indexer\StoreManager;
use Divante\VsbridgeIndexerCore\Cache\Processor as CacheProcessor;

/**
 * Class ProductCategory
 */
class ProductCategory implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var GenericIndexerHandler
     */
    private $indexHandler;


    /**
     * @var ProductAction
     */
    private $productAction;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var CacheProcessor
     */
    private $cacheProcessor;

    /**
     * Category constructor.
     *
     * @param CacheProcessor $cacheProcessor
     * @param GenericIndexerHandler $indexerHandler
     * @param StoreManager $storeManager
     * @param ProductAction $action
     */
    public function __construct(
        CacheProcessor $cacheProcessor,
        GenericIndexerHandler $indexerHandler,
        StoreManager $storeManager,
        ProductAction $action
    ) {
        $this->productAction = $action;
        $this->storeManager = $storeManager;
        $this->indexHandler = $indexerHandler;
        $this->cacheProcessor = $cacheProcessor;
    }

    /**
     * @inheritdoc
     */
    public function execute($ids)
    {
        $stores = $this->storeManager->getStores();

        foreach ($stores as $store) {
            $this->rebuild($store, $ids);
        }
    }

    /**
     * @inheritdoc
     */
    public function executeFull()
    {
        $stores = $this->storeManager->getStores();

        foreach ($stores as $store) {
            $this->rebuild($store);
        }
    }

    /**
     * @param $store
     * @param array $productIds
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function rebuild($store, array $productIds = [])
    {
        $this->indexHandler->updateIndex(
            $this->productAction->rebuild($store->getId(), $productIds),
            $store,
            ['category_data']
        );
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
