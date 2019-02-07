<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Api;

/**
 * Interface MappingInterface
 */
interface MappingInterface
{
    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type);

    /**
     * @return string
     */
    public function getType();

    /**
     * @return array
     */
    public function getMappingProperties();
}
