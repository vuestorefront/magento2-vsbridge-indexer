<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Indexer;

use Divante\VsbridgeIndexerCore\Config\GeneralSettings;
use Magento\Store\Model\StoreManagerInterface as StoreManagerInterface;

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
     * @var GeneralSettings
     */
    private $generalSettings;

    /**
     * StoreManager constructor.
     *
     * @param GeneralSettings $generalSettings
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        GeneralSettings $generalSettings,
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
        $allowStores = $this->generalSettings->getStoresToIndex();
        $stores = [];

        if (null === $storeId) {
            $allStores = $this->storeManager->getStores();

            foreach ($allStores as $store) {
                if (in_array($store->getId(), $allowStores)) {
                    $stores[] = $store;
                }
            }
        } elseif (in_array($storeId, $allowStores)) {
            $store = $this->storeManager->getStore($storeId);
            $stores = [$store];
        }

        return $stores;
    }
}
