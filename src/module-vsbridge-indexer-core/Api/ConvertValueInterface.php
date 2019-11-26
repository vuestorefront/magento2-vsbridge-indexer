<?php
/**
 * @package  Divante\VsbridgeIndexerCore
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Api;

/**
 * Interface ConvertValueInterface
 */
interface ConvertValueInterface
{
    /**
     * @param MappingInterface $mapping
     * @param string $field
     * @param string|array $value
     *
     * @return string|array|int|float
     */
    public function execute(MappingInterface $mapping, string $field, $value);
}
