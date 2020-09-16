<?php

namespace Divante\VsbridgeIndexerCore\Index\Indices;

use Divante\VsbridgeIndexerCore\Api\Index\TypeInterfaceFactory as TypeFactoryInterface;
use Divante\VsbridgeIndexerCore\Index\IndexSettings;
use Divante\VsbridgeIndexerCore\Index\MappingFactory;

/**
 * Class responsible for creating configuration for ES5
 */
class ConfigES5Parser implements ConfigParserInterface
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
        $types = [];

        foreach ($indexConfigData as $typeName => $typeConfigData) {
            $mapping = $this->mappingProviderProcessorFactory->get($typeConfigData['mapping']);
            $types[$typeName] = $this->typeFactory->create(
                [
                    'name' => $typeName,
                    'mapping' => $mapping,
                ]
            );
        }

        return [
            IndexSettings::DUMMY_INDEX_IDENTIFIER => ['types' => $types]
        ];
    }
}
