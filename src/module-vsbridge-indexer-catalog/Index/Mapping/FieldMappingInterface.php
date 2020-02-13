<?php declare(strict_types=1);
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Index\Mapping;

/**
 * Interface FieldMappingInterface
 */
interface FieldMappingInterface
{
    /**
     * Retrieve field mapping options
     *
     * @return array
     */
    public function get(): array;
}
