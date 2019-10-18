<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

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
     * @var \Divante\VsbridgeIndexerCore\Api\Index\TypeInterface[]
     */
    private $types;

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
     * @param string $name
     * @param string $identifier
     * @param bool $newIndex
     * @param array $types
     */
    public function __construct(
        string $name,
        string $identifier,
        bool $newIndex,
        array $types
    ) {
        $this->newIndex = $newIndex;
        $this->name = $name;
        $this->identifier = $identifier;
        $this->types = $this->prepareTypes($types);
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
