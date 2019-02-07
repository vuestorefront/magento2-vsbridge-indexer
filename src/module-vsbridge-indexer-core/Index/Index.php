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
     * @var
     */
    private $types;

    /**
     * Index constructor.
     *
     * @param string $name Index name
     * @param TypeInterface[] $types index types
     */
    public function __construct($name, array $types)
    {
        $this->name = $name;
        $this->types = $this->prepareTypes($types);
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
