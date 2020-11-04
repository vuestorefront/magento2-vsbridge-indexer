<?php


namespace Divante\VsbridgeIndexerCore\Indexer\Action;

/**
 * Rows reindex action
 */
class Rows extends AbstractAction
{
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
        }
    }
}
