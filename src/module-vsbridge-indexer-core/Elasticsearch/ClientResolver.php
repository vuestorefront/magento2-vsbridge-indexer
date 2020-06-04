<?php declare(strict_types=1);

/**
 * @package  Divante\VsbridgeIndexerCore
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Elasticsearch;

use Divante\VsbridgeIndexerCore\Api\Client\BuilderInterface as ClientBuilder;
use Divante\VsbridgeIndexerCore\Api\Client\ClientInterface;
use Divante\VsbridgeIndexerCore\Api\Client\ClientInterfaceFactory;
use Divante\VsbridgeIndexerCore\Api\Client\ConfigurationInterface;
use Divante\VsbridgeIndexerCore\Api\Client\ConfigurationInterfaceFactory;
use Divante\VsbridgeIndexerCore\Exception\ConnectionDisabledException;
use Divante\VsbridgeIndexerCore\System\GeneralConfigInterface;

/**
 * Class ClientResolver
 */
class ClientResolver
{
    /**
     * @var ClientInterface[]
     */
    private $clients = [];

    /**
     * @var GeneralConfigInterface
     */
    private $config;

    /**
     * @var ClientBuilder
     */
    private $clientBuilder;

    /**
     * @var ConfigurationInterfaceFactory
     */
    private $clientConfigurationFactory;

    /**
     * @var ClientInterfaceFactory
     */
    private $clientFactory;

    /**
     * ClientResolver constructor.
     *
     * @param GeneralConfigInterface $config
     * @param ClientBuilder $clientBuilder
     * @param ClientInterfaceFactory $clientFactory
     * @param ConfigurationInterfaceFactory $clientConfiguration
     */
    public function __construct(
        GeneralConfigInterface $config,
        ClientBuilder $clientBuilder,
        ClientInterfaceFactory $clientFactory,
        ConfigurationInterfaceFactory $clientConfiguration
    ) {
        $this->config = $config;
        $this->clientFactory = $clientFactory;
        $this->clientBuilder = $clientBuilder;
        $this->clientConfigurationFactory = $clientConfiguration;
    }

    /**
     * @inheritdoc
     */
    public function getClient(int $storeId): ClientInterface
    {
        if (!$this->config->isEnabled()) {
            throw new ConnectionDisabledException('ElasticSearch indexer is disabled.');
        }

        if (!isset($this->clients[$storeId])) {
            /** @var ConfigurationInterface $configuration */
            $configuration = $this->clientConfigurationFactory->create(['storeId' => $storeId]);
            $esClient = $this->clientBuilder->build($configuration->getOptions($storeId));
            $this->clients[$storeId] = $this->clientFactory->create(['client' => $esClient]);
        }

        return $this->clients[$storeId];
    }
}
