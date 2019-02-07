<?php
/**
 * @package  Divante\VsbridgeIndexerCore
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Config;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;

/**
 * Class GeneralSettings
 */
class GeneralSettings
{
    const GENERAL_SETTINGS_CONFIG_XML_PREFIX = 'vsbridge_indexer_settings/general_settings';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ClientConfiguration constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $storeId
     *
     * @return bool
     */
    public function canReindexStore($storeId)
    {
        $allowedStores = $this->getStoresToIndex();

        if (in_array($storeId, $allowedStores)) {
            return true;
        }

        return false;
    }

    /**
     * @return array|int|null|string
     */
    public function getStoresToIndex()
    {
        $stores = $this->getConfigParam('allowed_stores');

        if (null === $stores || '' === $stores) {
            $stores = [];
        } else {
            $stores = explode(',', $stores);
        }

        return $stores;
    }

    /**
     * @param string $configField
     *
     * @return string|null
     */
    private function getConfigParam(string $configField)
    {
        $path = self::GENERAL_SETTINGS_CONFIG_XML_PREFIX . '/' . $configField;

        return $this->scopeConfig->getValue($path);
    }
}
