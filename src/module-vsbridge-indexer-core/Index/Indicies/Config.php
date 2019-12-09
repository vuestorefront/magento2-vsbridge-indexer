<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Index\Indicies;

use Divante\VsbridgeIndexerCore\Indexer\DataProviderProcessorFactory;
use Divante\VsbridgeIndexerCore\Indexer\MappingProcessorFactory;

/**
 * Class Config
 */
class Config
{
    /**
     * @var DataProviderProcessorFactory
     */
    private $dataProviderFactoryProcessor;

    /**
     * @var MappingProcessorFactory
     */
    private $mappingProviderProcessorFactory;

    /**
     * @var \Divante\VsbridgeIndexerCore\Indexer\DataProvider\TransactionKey
     */
    private $transactionKey;

    /**
     * Config\Data
     */
    private $configData;

    /**
     * Config constructor.
     *
     * @param Config\Data $configData
     * @param \Divante\VsbridgeIndexerCore\Indexer\DataProvider\TransactionKey $transactionKey
     * @param MappingProcessorFactory $mappingProcessorFactory
     * @param DataProviderProcessorFactory $dataProviderFactoryProcessor
     */
    public function __construct(
        Config\Data $configData,
        \Divante\VsbridgeIndexerCore\Indexer\DataProvider\TransactionKey $transactionKey,
        MappingProcessorFactory $mappingProcessorFactory,
        DataProviderProcessorFactory $dataProviderFactoryProcessor
    ) {
        $this->configData = $configData;
        $this->transactionKey = $transactionKey;
        $this->mappingProviderProcessorFactory = $mappingProcessorFactory;
        $this->dataProviderFactoryProcessor = $dataProviderFactoryProcessor;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        $configData = $this->configData->get();
        $indicesConfig = [];

        foreach ($configData as $indexIdentifier => $indexConfig) {
            $indicesConfig[$indexIdentifier] = $this->initIndexConfig($indexConfig);
        }

        return $indicesConfig;
    }

    /**
     * @param array $indexConfigData
     *
     * @return array
     */
    private function initIndexConfig(array $indexConfigData): array
    {
        $dataProviders['transaction_key'] = $this->transactionKey;
        $mapping = null;

        foreach ($indexConfigData['data_providers'] as $dataProviderName => $dataProviderClass) {
            $dataProviders[$dataProviderName] =
                $this->dataProviderFactoryProcessor->get($dataProviderClass);
        }

        if (isset($indexConfigData['mapping'])) {
            $mapping = $this->mappingProviderProcessorFactory->get($indexConfigData['mapping']);
        }

       $config = [
           'dataProviders' => $dataProviders,
           'mapping' => $mapping,
       ];

        return $config;
    }
}
