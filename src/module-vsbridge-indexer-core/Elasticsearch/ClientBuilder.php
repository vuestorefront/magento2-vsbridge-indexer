<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Elasticsearch;

use Divante\VsbridgeIndexerCore\Api\Client\BuilderInterface as ClientBuilderInterface;

/**
 * Class ClientBuilder
 */
class ClientBuilder implements ClientBuilderInterface
{
    /**
     * @var array
     */
    private $defaultOptions = [
        'host' => 'localhost',
        'port' => '9200',
        'enable_http_auth' => false,
        'auth_user' => null,
        'auth_pwd' => null,
        'timeout' => 30,        // ten second timeout
        'connect_timeout' => 30
    ];

    /**
     * @inheritdoc
     */
    public function build(array $options = [])
    {
        $options = array_merge($this->defaultOptions, $options);
        $esClientBuilder = \Elasticsearch\ClientBuilder::create();
        $host = $this->getHost($options);

        if (!empty($host)) {
            $esClientBuilder->setHosts([$host]);
        }

        return $esClientBuilder->build();
    }

    /**
     * Return hosts config used to connect to the cluster.
     *
     * @param array $options Client options.
     *
     * @return array
     */
    private function getHost(array $options)
    {
        $scheme = 'http';

        if (isset($options['enable_https_mode'])) {
            $scheme = 'https';
        } elseif (isset($options['schema'])) {
            $scheme = $options['schema'];
        }

        $currentHostConfig = [
            'host' => $options['host'],
            'port' => $options['port'],
            'scheme' => $scheme,
        ];

        if ($options['enable_http_auth']) {
            $currentHostConfig['user'] = $options['auth_user'];
            $currentHostConfig['pass'] = $options['auth_pwd'];
        }

        return $currentHostConfig;
    }
}
