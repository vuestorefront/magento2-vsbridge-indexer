<?php

declare(strict_types=1);

namespace Divante\VsbridgeIndexerCore\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @inheridoc
 */
class ElasticsearchResolver implements ElasticsearchResolverInterface
{
    const ES_VERSION_XPATH = 'vsbridge_indexer_settings/general_settings/es_version';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ElasticsearchResolver constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheridoc
     *
     * @return string
     */
    public function getVersion(): string
    {
        return (string)$this->scopeConfig->getValue(self::ES_VERSION_XPATH);
    }
}
