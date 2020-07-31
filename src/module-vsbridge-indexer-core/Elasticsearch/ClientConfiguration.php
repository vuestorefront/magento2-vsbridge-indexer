<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Elasticsearch;

use Divante\VsbridgeIndexerCore\Api\Client\ConfigurationInterface as ClientConfigurationInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ClientConfiguration
 */
class ClientConfiguration implements ClientConfigurationInterface
{
    const ES_CLIENT_CONFIG_XML_PREFIX = 'vsbridge_indexer_settings/es_client';

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
     * @return array
     */
    public function getOptions(int $storeId)
    {
        $options = [
            'host' => $this->getHost($storeId),
            'port' => $this->getPort($storeId),
            'scheme' => $this->getScheme($storeId),
            'enable_http_auth' => $this->isHttpAuthEnabled($storeId),
            'auth_user' => $this->getHttpAuthUser($storeId),
            'auth_pwd' => $this->getHttpAuthPassword($storeId),
        ];

        return $options;
    }

    /**
     * @return string
     */
    public function getHost(int $storeId)
    {
        return (string)$this->getConfigParam('host', $storeId);
    }

    /**
     * @return string
     */
    public function getPort(int $storeId)
    {
        return (string)$this->getConfigParam('port', $storeId);
    }

    /**
     * @return string
     */
    public function getScheme(int $storeId)
    {
        return (bool)$this->isHttpsModeEnabled($storeId) ? 'https' : 'http';
    }

    /**
     * @return bool
     */
    public function isHttpsModeEnabled(int $storeId)
    {
        return (bool)$this->getConfigParam('enable_https_mode', $storeId);
    }

    /**
     * @return bool
     */
    public function isHttpAuthEnabled(int $storeId)
    {
        $authEnabled = (bool)$this->getConfigParam('enable_http_auth', $storeId);

        return $authEnabled && !empty($this->getHttpAuthUser($storeId)) && !empty($this->getHttpAuthPassword($storeId));
    }

    /**
     * @return string
     */
    public function getHttpAuthUser(int $storeId)
    {
        return (string)$this->getConfigParam('auth_user', $storeId);
    }

    /**
     * @return string
     */
    public function getHttpAuthPassword(int $storeId)
    {
        return (string)$this->getConfigParam('auth_pwd', $storeId);
    }

    /**
     * @param string $configField
     *
     * @return string|null
     */
    private function getConfigParam(string $configField, $storeId)
    {
        $path = self::ES_CLIENT_CONFIG_XML_PREFIX . '/' . $configField;

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
