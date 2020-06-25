<?php

namespace Divante\VsbridgeIndexerCore\Index;

use Divante\VsbridgeIndexerCore\Api\IndexInterface;
use Divante\VsbridgeIndexerCore\Api\Index\TypeInterface;

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
     * Index types.
     *
     * @var TypeInterface[]
     */
    private $types;

    /**
     * @var string
     */
    private $alias;

    /**
     * Index constructor.
     *
     * @param string $name
     * @param string $alias
     * @param array $types
     */
    public function __construct(
        string $name,
        string $alias,
        array $types
    ) {
        $this->alias = $alias;
        $this->name = $name;
        $this->types = $this->prepareTypes($types);
    }

    /**
     * @inheritdoc
     */
    public function isNew()
    {
        return $this->alias !== $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param TypeInterface[] $types
     *
     * @return TypeInterface[]
     */
    private function prepareTypes($types)
    {
        $preparedTypes = [];

        foreach ($types as $type) {
            $preparedTypes[$type->getName()] = $type;
        }

        return $preparedTypes;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @inheritdoc
     */
    public function getType($typeName)
    {
        if (!isset($this->types[$typeName])) {
            throw new \InvalidArgumentException("Type $typeName is not available in index.");
        }

        return $this->types[$typeName];
    }
}
