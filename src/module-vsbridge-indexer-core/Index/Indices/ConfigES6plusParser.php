<?php

namespace Divante\VsbridgeIndexerCore\Index\Indices;

use Divante\VsbridgeIndexerCore\Api\Index\TypeInterfaceFactory as TypeFactoryInterface;
use Divante\VsbridgeIndexerCore\Api\IndexInterface;
use Divante\VsbridgeIndexerCore\Index\MappingFactory;

/**
 * Parser configuration to much requirements for ES 6 plus version. We can have only one type in index.
 */
class ConfigES6plusParser implements ConfigParserInterface
{
    /**
     * Factory used to build mapping types.
     *
     * @var TypeFactoryInterface
     */
    private $typeFactory;

    /**
     * @var MappingFactory
     */
    private $mappingProviderProcessorFactory;

    /**
     * Config constructor.
     *
     * @param TypeFactoryInterface $typeInterfaceFactory
     * @param MappingFactory $mappingProcessorFactory
     */
    public function __construct(
        TypeFactoryInterface $typeInterfaceFactory,
        MappingFactory $mappingProcessorFactory
    ) {
        $this->mappingProviderProcessorFactory = $mappingProcessorFactory;
        $this->typeFactory = $typeInterfaceFactory;
    }

    /**
     * @param array $indexConfigData
     *
     * @return array
     */
    public function parse(array $indexConfigData): array
    {
        $indices = [];

        foreach ($indexConfigData as $typeName => $typeConfigData) {
            $mapping = $this->mappingProviderProcessorFactory->get($typeConfigData['mapping']);
            $type = $this->typeFactory->create(
                [
                    'name' => $typeName,
                    'mapping' => $mapping,
                ]
            );

            $indices[$typeName] = ['types' => [IndexInterface::DUMMY_INDEX_TYPE => $type]];
        }

        return $indices;
    }
}
