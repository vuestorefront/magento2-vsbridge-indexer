<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;

/**
 * Class ConfigSettings
 */
class ConfigSettings
{
    const CATALOG_SETTINGS_XML_PREFIX = 'vsbridge_indexer_settings/catalog_settings';

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
     * @return bool
     */
    public function useMagentoUrlKeys()
    {
        return (bool) $this->getConfigParam('use_magento_url_keys');
    }

    /**
     * @return bool
     */
    public function syncTierPrices()
    {
        return (bool) $this->getConfigParam('sync_tier_prices');
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function getAllowedProductTypes($storeId)
    {
        $types = $this->getConfigParam('allowed_product_types', $storeId);

        if (null === $types || '' === $types) {
            $types = [];
        } else {
            $types = explode(',', $types);
        }

        return $types;
    }

    /**
     * @param string $configField
     * @param int|null $storeId
     *
     * @return string|null
     */
    private function getConfigParam(string $configField, $storeId = null)
    {
        $path = self::CATALOG_SETTINGS_XML_PREFIX . '/' . $configField;

        if ($storeId) {
            return $this->scopeConfig->getValue($path, 'stores', $storeId);
        }

        return $this->scopeConfig->getValue($path);
    }
}
