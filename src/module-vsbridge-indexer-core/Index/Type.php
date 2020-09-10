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
     * Type dataProviders.
     *
     * @var
     */
    private $dataProviders;

    /**
     * Type constructor.
     *
     * @param $name
     * @param MappingInterface|null $mapping
     * @param array $dataProviders
     */
    public function __construct($name, MappingInterface $mapping = null, array $dataProviders)
    {
        $this->name = $name;
        $this->mapping = $mapping;
        $this->dataProviders = $dataProviders;
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

    /**
     * @inheritdoc
     */
    public function getDataProviders()
    {
        ksort($this->dataProviders);
        return $this->dataProviders;
    }

    /**
     * @inheritdoc
     */
    public function getDataProvider(string $name)
    {
        if (!isset($this->dataProviders[$name])) {
            throw new \Exception("DataProvider $name does not exists.");
        }

        return $this->dataProviders[$name];
    }
}
