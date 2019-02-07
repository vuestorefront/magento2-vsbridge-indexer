<?php
/**
 * @package  Divante\VsbridgeIndexerCore
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Cache;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;

/**
 * Class Settings
 */
class Settings
{
    /**
     * XML PATH Prefix for redis cache settings
     */
    const CACHE_SETTINGS_XML_PREFIX = 'vsbridge_indexer_settings/redis_cache_settings';

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
     * @param int $storeId
     *
     * @return bool
     */
    public function clearCache($storeId)
    {
        return (bool) $this->getConfigParam('clear_cache', $storeId);
    }

    /**
     * @param int $storeId
     *
     * @return string
     */
    public function getVsfBaseUrl($storeId)
    {
        return (string) $this->getConfigParam('vsf_base_url', $storeId);
    }

    /**
     * @param int $storeId
     *
     * @return string
     */
    public function getInvalidateCacheKey($storeId)
    {
        return (string) $this->getConfigParam('invalidate_cache_key', $storeId);
    }

    /**
     * @param int $storeId
     *
     * @return int
     */
    public function getTimeout($storeId)
    {
        return (int) $this->getConfigParam('connection_timeout', $storeId);
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function getConnectionOptions($storeId)
    {
        $options = ['timeout' => $this->getTimeout($storeId)];

        return $options;
    }

    /**
     * @param string $configField
     * @param int $storeId
     *
     * @return string|null
     */
    private function getConfigParam(string $configField, $storeId)
    {
        $path = self::CACHE_SETTINGS_XML_PREFIX . '/' . $configField;

        return $this->scopeConfig->getValue($path, 'stores', $storeId);
    }
}
