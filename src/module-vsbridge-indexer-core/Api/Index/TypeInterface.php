<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Api\Index;

/**
 * Interface TypeInterface
 */
interface TypeInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return \Divante\VsbridgeIndexerCore\Api\MappingInterface
     */
    public function getMapping();
}
