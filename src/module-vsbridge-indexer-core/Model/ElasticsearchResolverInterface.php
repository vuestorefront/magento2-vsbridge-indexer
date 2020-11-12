<?php

namespace Divante\VsbridgeIndexerCore\Model;

/**
 * Elasticsearch resolver.
 */
interface ElasticsearchResolverInterface
{
    const DEFAULT_ES_VERSION = 'elasticsearch5';

    const ES_6_PLUS_VERSION = 'elasticsearch6plus';

    /**
     * It returns string identifier of elasticsearch version that is currently chosen in configuration
     *
     * @return string
     */
    public function getVersion(): string;
}
