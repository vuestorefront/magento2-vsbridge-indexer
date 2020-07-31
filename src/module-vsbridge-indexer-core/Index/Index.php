<?php

namespace Divante\VsbridgeIndexerCore\Index;

use Divante\VsbridgeIndexerCore\Api\IndexInterface;
use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeIndexerCore\Api\MappingInterface;

/**
 * Class Index
 */
class Index implements IndexInterface
{

    /**
     * Name of the index.
     *
     * @var string
     */
    private $name;

    /**
     * Type mapping.
     *
     * @var
     */
    private $mapping;

    /**
     * Type dataProviders.
     *N
     * @var
     */
    private $dataProviders;

    /**
     * @var string
     */
    private $identifier;

    /**
     * Index constructor.
     *
     * @param string $name
     * @param string $identifier
     * @param array $dataProviders
     * @param MappingInterface|null $mapping
     */
    public function __construct(
        string $name,
        string $identifier,
        array $dataProviders,
        MappingInterface $mapping = null
    ) {
        $this->identifier = $identifier;
        $this->name = $name;
        $this->mapping = $mapping;
        $this->dataProviders = $dataProviders;
    }

    /**
     * @inheritdoc
     */
    public function isNew()
    {
        return $this->identifier !== $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return \Divante\VsbridgeIndexerCore\Api\Client\ClientInterface::ES_DUMMY_TYPE_NAME;
    }

    /**
     * @inheritdoc
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * @inheritdoc
     */
    public function getDataProviders()
    {
        return $this->dataProviders;
    }
}
