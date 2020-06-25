<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Index\Indicies;

use Divante\VsbridgeIndexerCore\Api\Index\TypeInterfaceFactory as TypeFactoryInterface;
use Divante\VsbridgeIndexerCore\Indexer\DataProviderProcessorFactory;
use Divante\VsbridgeIndexerCore\Indexer\MappingProcessorFactory;

/**
 * Class Config
 */
class Config
{
    /**
     * Factory used to build mapping types.
     *
     * @var TypeFactoryInterface
     */
    private $typeFactory;

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
     * @param TypeFactoryInterface $typeInterfaceFactory
     * @param MappingProcessorFactory $mappingProcessorFactory
     * @param DataProviderProcessorFactory $dataProviderFactoryProcessor
     */
    public function __construct(
        Config\Data $configData,
        \Divante\VsbridgeIndexerCore\Indexer\DataProvider\TransactionKey $transactionKey,
        TypeFactoryInterface $typeInterfaceFactory,
        MappingProcessorFactory $mappingProcessorFactory,
        DataProviderProcessorFactory $dataProviderFactoryProcessor
    ) {
        $this->configData = $configData;
        $this->transactionKey = $transactionKey;
        $this->mappingProviderProcessorFactory = $mappingProcessorFactory;
        $this->dataProviderFactoryProcessor = $dataProviderFactoryProcessor;
        $this->typeFactory = $typeInterfaceFactory;
    }

    /**
     * @return array
     */
    public function get()
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
    private function initIndexConfig(array $indexConfigData)
    {
        $types = [];

        foreach ($indexConfigData['types'] as $typeName => $typeConfigData) {
            $dataProviders = ['transaction_key' => $this->transactionKey];

            foreach ($typeConfigData['data_providers'] as $dataProviderName => $dataProviderClass) {
                $dataProviders[$dataProviderName] =
                    $this->dataProviderFactoryProcessor->get($dataProviderClass);
            }

            $mapping = null;

            if (isset($typeConfigData['mapping'][0])) {
                $mapping = $this->mappingProviderProcessorFactory->get($typeConfigData['mapping'][0]);
            }

            $types[$typeName] = $this->typeFactory->create(
                [
                    'name' => $typeName,
                    'dataProviders' => $dataProviders,
                    'mapping' => $mapping,
                ]
            );
        }

        return ['types' => $types];
    }
}
