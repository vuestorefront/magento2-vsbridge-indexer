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
use Divante\VsbridgeIndexerCatalog\Api\Data\CatalogConfigurationInterface;

/**
 * Class GetConfigurableOptions
 */
class GetConfigurableOptions
{
    /**
     * @var CatalogConfigurationInterface
     */
    private $catalogSettings;

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
     * @param LoadOptionById $loadOptionById
     * @param SortValues $sortValues
     * @param CatalogConfigurationInterface $catalogSettings
     */
    public function __construct(
        LoadOptionById $loadOptionById,
        SortValues $sortValues,
        CatalogConfigurationInterface $catalogSettings
    ) {
        $this->loadOptionById = $loadOptionById;
        $this->catalogSettings = $catalogSettings;
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

            if (!empty($option)) {
                if (!$this->catalogSettings->addSwatchesToConfigurableOptions()) {
                    unset($option['swatch']);
                }

                $options[] = $option;
            }
        }

        return $this->sortValues->execute($options);
    }
}
