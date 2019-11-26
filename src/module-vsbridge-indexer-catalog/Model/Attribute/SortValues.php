<?php

declare(strict_types = 1);

/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Attribute;

/**
 * Class SortValues
 */
class SortValues
{
    /**
     * @param array $options
     *
     * @return array
     */
    public function execute(array $options)
    {
        usort($options, [$this, 'sortOptions']);

        return $options;
    }

    /**
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    public function sortOptions($a, $b)
    {
        $aSizePos = $a['sort_order'] ?? 0;
        $bSizePos = $b['sort_order'] ?? 0;

        if ($aSizePos === $bSizePos) {
            return 0;
        }

        return ($aSizePos > $bSizePos) ? 1 : -1;
    }
}
