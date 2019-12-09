<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

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
     * @var bool
     */
    private $newIndex = false;

    /**
     * Index constructor.
     *
     * @param string $name Index name
     * @param string $newIndex
     * @param string $identifier
     * @param DataProviderInterface[] $dataProviders index data providers
     * @param MappingInterface|null mapping
     */
    public function __construct(
        string $name,
        bool $newIndex,
        string $identifier,
        array $dataProviders,
        MappingInterface $mapping = null
    ) {
        $this->name = $name;
        $this->newIndex = $newIndex;
        $this->identifier = $identifier;
        $this->mapping = $mapping;
        $this->dataProviders = $dataProviders;
    }

    /**
     * @inheritdoc
     */
    public function isNew()
    {
        return $this->newIndex;
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
