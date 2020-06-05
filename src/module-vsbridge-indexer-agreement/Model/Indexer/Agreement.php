<?php
/**
 * Copyright Divante Sp. z o.o.
 * See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerAgreement\Model\Indexer;

use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterfaceAlias;
use Divante\VsbridgeIndexerCore\Indexer\StoreManager;
use Divante\VsbridgeIndexerCore\Indexer\GenericIndexerHandler;
use Divante\VsbridgeIndexerAgreement\Model\Indexer\Action\Agreement as AgreementAction;

class Agreement implements ActionInterface, MviewActionInterfaceAlias
{
    /**
     * @var GenericIndexerHandler
     */
    private $indexHandler;

    /**
     * @var AgreementAction
     */
    private $agreementAction;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * Agreement constructor.
     *
     * @param GenericIndexerHandler $indexerHandler
     * @param StoreManager $storeManager
     * @param AgreementAction $action
     */
    public function __construct(
        GenericIndexerHandler $indexerHandler,
        StoreManager $storeManager,
        AgreementAction $action
    ) {
        $this->indexHandler = $indexerHandler;
        $this->agreementAction = $action;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function execute($ids)
    {
        $stores = $this->storeManager->getStores();

        foreach ($stores as $store) {
            $this->indexHandler->saveIndex($this->agreementAction->rebuild($store->getId(), $ids), $store);
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
            $this->indexHandler->saveIndex($this->agreementAction->rebuild($store->getId()), $store);
            $this->indexHandler->cleanUpByTransactionKey($store);
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
