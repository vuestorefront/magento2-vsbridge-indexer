<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Index;

use Divante\VsbridgeIndexerCore\Api\Index\TypeInterface;
use Divante\VsbridgeIndexerCore\Api\MappingInterface;

/**
 * Class Type
 */
class Type implements TypeInterface
{
    /**
     * Type name.
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
     * Type constructor.
     *
     * @param $name
     * @param MappingInterface|null $mapping
     */
    public function __construct($name, MappingInterface $mapping = null)
    {
        $this->name = $name;
        $this->mapping = $mapping;
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
    public function getMapping()
    {
        return $this->mapping;
    }
}
