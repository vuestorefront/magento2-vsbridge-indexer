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
 * Class StoreManager
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
     * @var
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
     * @param null $storeId
     *
     * @return array|\Magento\Store\Api\Data\StoreInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStores($storeId = null)
    {
        if (null === $this->loadedStores) {
            $allowStores = $this->generalSettings->getStoresToIndex();
            $stores = [];

            if (null === $storeId) {
                $allStores = $this->storeManager->getStores();

                foreach ($allStores as $store) {
                    if (in_array($store->getId(), $allowStores)) {
                        $stores[] = $store;
                    }
                }
            } else {
                $store = $this->storeManager->getStore($storeId);

                if (in_array($store->getId(), $allowStores)) {
                    $stores = [$store];
                }
            }

            $this->loadedStores = $stores;
        }

        return $this->loadedStores;
    }

    /**
     * @param array $stores
     */
    public function setLoadedStores(array $stores)
    {
        $this->loadedStores = $stores;
    }
}
