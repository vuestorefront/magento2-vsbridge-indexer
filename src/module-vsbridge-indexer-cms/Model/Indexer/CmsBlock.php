<?php
/**
 * @package   Divante\VsbridgeIndexerCms
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCms\Model\Indexer;

use Divante\VsbridgeIndexerCore\Indexer\StoreManager;
use Divante\VsbridgeIndexerCms\Model\Indexer\Action\CmsBlock as CmsBlockAction;
use Divante\VsbridgeIndexerCore\Indexer\GenericIndexerHandler;

/**
 * Class CmsBlock
 */
class CmsBlock implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var GenericIndexerHandler
     */
    private $indexHandler;

    /**
     * @var CmsBlockAction
     */
    private $cmsBlockAction;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * CmsBlock constructor.
     *
     * @param GenericIndexerHandler $indexerHandler
     * @param StoreManager $storeManager
     * @param CmsBlockAction $action
     */
    public function __construct(
        GenericIndexerHandler $indexerHandler,
        StoreManager $storeManager,
        CmsBlockAction $action
    ) {
        $this->indexHandler = $indexerHandler;
        $this->cmsBlockAction = $action;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function execute($ids)
    {
        $stores = $this->storeManager->getStores();

        foreach ($stores as $store) {
            $this->indexHandler->saveIndex($this->cmsBlockAction->rebuild($store->getId(), $ids), $store);
            $this->indexHandler->cleanUpByTransactionKey($store, $ids);
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
            $this->indexHandler->saveIndex($this->cmsBlockAction->rebuild($store->getId()), $store);
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
