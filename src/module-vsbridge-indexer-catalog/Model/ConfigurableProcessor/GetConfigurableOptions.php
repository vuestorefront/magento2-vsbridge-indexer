<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ConfigurableProcessor;

use Divante\VsbridgeIndexerCatalog\Model\Attribute\LoadOptionById;
use Divante\VsbridgeIndexerCatalog\Model\Attribute\SortValues;

/**
 * Class GetConfigurableOptions
 */
class GetConfigurableOptions
{
    /**
     * @var LoadOptionById
     */
    private $loadOptionById;

    /**
     * @var SortValues
     */
    private $sortValues;

    /**
     * GetConfigurableOptions constructor.
     *
     * @param LoadOptionById $loadOptions
     * @param SortValues $sortValues
     */
    public function __construct(LoadOptionById $loadOptions, SortValues $sortValues)
    {
        $this->loadOptionById = $loadOptions;
        $this->sortValues = $sortValues;
    }

    /**
     * @param string $attributeCode
     * @param int $storeId
     * @param array $configurableChildren
     *
     * @return array
     */
    public function execute(string $attributeCode, int $storeId, array $configurableChildren): array
    {
        $values = [];

        foreach ($configurableChildren as $child) {
            if (isset($child[$attributeCode])) {
                $value = $child[$attributeCode];

                if (isset($value)) {
                    $values[] = (int) $value;
                }
            }
        }

        $values = array_values(array_unique($values));
        $options = [];

        foreach ($values as $value) {
            $option = $this->loadOptionById->execute($attributeCode, $value, $storeId);
            $options[] = $option;
        }

        return $this->sortValues->execute($options);
    }
}
