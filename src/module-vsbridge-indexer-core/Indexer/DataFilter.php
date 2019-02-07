<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Indexer;

/**
 * Class DataFilter
 */
class DataFilter
{
    /**
     * @var array
     */
    private $integerProperties = [];

    /**
     * DataFilter constructor.
     *
     * @param array $integerProperties
     */
    public function __construct(array $integerProperties = [])
    {
        $this->integerProperties = $integerProperties;
    }

    /**
     * @param array      $dtoToFilter
     * @param array|null $blackList
     *
     * @return array
     */
    public function execute(array $dtoToFilter, array $blackList = null)
    {
        foreach ($dtoToFilter as $key => $val) {
            if ($blackList && in_array($key, $blackList)) {
                unset($dtoToFilter[$key]);
            } else {
                if (strstr($key, 'is_') || strstr($key, 'has_')) {
                    $dtoToFilter[$key] = (bool)$val;
                } else {
                    if (in_array($key, $this->integerProperties)) {
                        $dtoToFilter[$key] = (int)$val;
                    }
                }
            }
        }

        return $dtoToFilter;
    }
}
