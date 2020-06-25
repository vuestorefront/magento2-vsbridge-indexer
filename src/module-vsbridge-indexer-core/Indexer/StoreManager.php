<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

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
     * @param int|null $storeId
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
}
