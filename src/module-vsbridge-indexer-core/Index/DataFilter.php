<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Index;

/**
 * Class responsible for removing fields from array (if provided) and casting values
 * TODO check if still need this to use this
 */
class DataFilter
{
    /**
     * @var array
     */
    private $integerProperties = [];

    /**
     * @var array
     */
    private $floatProperties = [];

    /**
     * DataFilter constructor.
     *
     * @param array $integerProperties
     * @param array $floatProperties
     */
    public function __construct(
        array $integerProperties = [],
        array $floatProperties = []
    ) {
        $this->integerProperties = $integerProperties;
        $this->floatProperties = $floatProperties;
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
                    } elseif (in_array($key, $this->floatProperties)) {
                        $dtoToFilter[$key] = (float)$val;
                    }
                }
            }
        }

        return $dtoToFilter;
    }
}
