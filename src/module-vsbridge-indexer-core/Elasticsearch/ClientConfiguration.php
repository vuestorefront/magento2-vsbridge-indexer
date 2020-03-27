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
     * @var int
     */
    private $storeId = null;

    /**
     * ClientConfiguration constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param int $storeId
     */
    public function __construct(
        int $storeId,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeId = $storeId;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $options = [
            'host' => $this->getHost(),
            'port' => $this->getPort(),
            'scheme' => $this->getScheme(),
            'enable_http_auth' => $this->isHttpAuthEnabled(),
            'auth_user' => $this->getHttpAuthUser(),
            'auth_pwd' => $this->getHttpAuthPassword(),
        ];

        return $options;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return (string)$this->getConfigParam('host');
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return (string)$this->getConfigParam('port');
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return (bool)$this->isHttpsModeEnabled() ? 'https' : 'http';
    }

    /**
     * @return bool
     */
    public function isHttpsModeEnabled()
    {
        return (bool)$this->getConfigParam('enable_https_mode');
    }

    /**
     * @return bool
     */
    public function isHttpAuthEnabled()
    {
        $authEnabled = (bool)$this->getConfigParam('enable_http_auth');

        return $authEnabled && !empty($this->getHttpAuthUser()) && !empty($this->getHttpAuthPassword());
    }

    /**
     * @return string
     */
    public function getHttpAuthUser()
    {
        return (string)$this->getConfigParam('auth_user');
    }

    /**
     * @return string
     */
    public function getHttpAuthPassword()
    {
        return (string)$this->getConfigParam('auth_pwd');
    }

    /**
     * @param string $configField
     *
     * @return string|null
     */
    private function getConfigParam(string $configField)
    {
        $path = self::ES_CLIENT_CONFIG_XML_PREFIX . '/' . $configField;

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $this->storeId);
    }
}
