<?php

declare(strict_types=1);

/**
 * @package  Divante\VsbridgeIndexerCore
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Cache;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;

/**
 * @inheritdoc
 */
class Config implements ConfigInterface
{
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
    public function clearCache($storeId): bool
    {
        return (bool) $this->getConfigParam(self::CLEAR_CACHE_FIELD, $storeId);
    }

    /**
     * @param int $storeId
     *
     * @return int
     */
    public function getInvalidateEntitiesBatchSize(int $storeId): int
    {
        return (int) $this->getConfigParam(self::INVALIDATE_CACHE_ENTITIES_BATCH_SIZE_FIELD, $storeId);
    }

    /**
     * @param int $storeId
     *
     * @return string
     */
    public function getVsfBaseUrl($storeId): string
    {
        return (string) $this->getConfigParam(self::VSF_BASE_URL_FIELD, $storeId);
    }

    /**
     * @param int $storeId
     *
     * @return string
     */
    public function getInvalidateCacheKey($storeId): string
    {
        return (string) $this->getConfigParam(self::INVALIDATE_CACHE_FIELD, $storeId);
    }

    /**
     * @param int $storeId
     *
     * @return int
     */
    public function getTimeout($storeId): int
    {
        return (int) $this->getConfigParam(self::CONNECTION_TIMEOUT_FIELD, $storeId);
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function getConnectionOptions($storeId): array
    {
        return ['timeout' => $this->getTimeout($storeId)];
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
