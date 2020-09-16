<?php

namespace Divante\VsbridgeIndexerCore\Index\Indices;

use Divante\VsbridgeIndexerCore\Exception\ConfigParserNotExistException;
use Divante\VsbridgeIndexerCore\Model\ElasticsearchResolverInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Resolver config for active elasticsearch version
 */
class ConfigResolver
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ElasticsearchResolverInterface
     */
    private $elasticsearchResolver;

    /**
     * @var array
     */
    private $configParsers;

    /**
     * Config\Data
     */
    private $configData;

    /**
     * ConfigResolver constructor.
     * @param Config\Data $configData
     * @param ObjectManagerInterface $objectManager
     * @param ElasticsearchResolverInterface $elasticsearchResolver
     * @param array $configParsers
     */
    public function __construct(
        Config\Data $configData,
        ObjectManagerInterface $objectManager,
        ElasticsearchResolverInterface $elasticsearchResolver,
        array $configParsers
    ) {
        $this->configData = $configData;
        $this->configParsers = $configParsers;
        $this->objectManager = $objectManager;
        $this->elasticsearchResolver = $elasticsearchResolver;
    }

    /**
     * @return array
     */
    public function resolve(): array
    {
        $currentEsVersion = $this->elasticsearchResolver->getVersion();

        if (!isset($this->configParsers[$currentEsVersion])) {
            throw new ConfigParserNotExistException(__('There is no parser for: ' . $currentEsVersion));
        }

        /** @var ConfigParserInterface $configParser */
        $configParser = $this->objectManager->get($this->configParsers[$currentEsVersion]);

        return $configParser->parse($this->configData->get());
    }
}
