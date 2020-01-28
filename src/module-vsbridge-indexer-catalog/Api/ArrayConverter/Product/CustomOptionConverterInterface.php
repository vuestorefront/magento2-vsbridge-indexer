<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Api\ArrayConverter\Product;

/**
 * Interface CustomOptionConverterInterface
 */
interface CustomOptionConverterInterface
{
    /**
     * @param array $options
     * @param array $optionValues
     *
     * @return array
     */
    public function process(array $options, array $optionValues): array;
}
