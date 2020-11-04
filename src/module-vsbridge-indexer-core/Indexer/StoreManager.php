<?php

namespace Divante\VsbridgeIndexerCore\Indexer;

use Divante\VsbridgeIndexerCore\System\GeneralConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class StoreManager is responsible for getting stores allowed to reindex
 */
class StoreManager
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var GeneralConfigInterface
     */
    private $generalSettings;

    /**
     * @var array|null
     */
    private $loadedStores = null;

    /**
     * StoreManager constructor.
     *
     * @param GeneralConfigInterface $generalSettings
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        GeneralConfigInterface $generalSettings,
        StoreManagerInterface $storeManager
    ) {
        $this->generalSettings = $generalSettings;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $store
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isStoreAllowedToReindex(string $store)
    {
        $storeModel = $this->storeManager->getStore($store);
        $allowedStores = $this->getAllStoresAllowedToReindex();

        return isset($allowedStores[$storeModel->getCode()]);
    }

    /**
     * @param int|string|null $storeId
     *
     * @return array|\Magento\Store\Api\Data\StoreInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStores($storeId = null)
    {
        if ($this->loadedStores) {
            return $this->loadedStores;
        }

        $allowedStoreIds = $this->generalSettings->getStoresToIndex();
        $allowedStores = [];

        if (null === $storeId) {
            $stores = $this->storeManager->getStores();
        } else {
            $stores = [$this->storeManager->getStore($storeId)];
        }

        foreach ($stores as $store) {
            if (in_array($store->getId(), $allowedStoreIds)) {
                $allowedStores[] = $store;
            }
        }

        return $this->loadedStores = $allowedStores;
    }

    /**
     * @param array $stores
     */
    public function override(array $stores)
    {
        $this->loadedStores = $stores;
    }

    /**
     * @return \Magento\Store\Api\Data\StoreInterface[]
     */
    private function getAllStoresAllowedToReindex()
    {
        $allowedStoreIds = $this->generalSettings->getStoresToIndex();
        $storesByCode = $this->storeManager->getStores(false, true);

        return array_filter($storesByCode, function ($store) use ($allowedStoreIds) {
            return in_array($store->getId(), $allowedStoreIds);
        });
    }
}
