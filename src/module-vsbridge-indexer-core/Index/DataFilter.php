<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Index;

/**
 * Class responsible for removing fields from array
 */
class DataFilter
{
    /**
     * @param array $dtoToFilter
     * @param array $blackList
     *
     * @return array
     */
    public function execute(array $dtoToFilter, array $blackList)
    {
        foreach ($dtoToFilter as $key => $val) {
            if (in_array($key, $blackList)) {
                unset($dtoToFilter[$key]);
            }
        }

        return $dtoToFilter;
    }
}
