<?php declare(strict_types = 1);

/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\ArrayConverter\Product;

use Divante\VsbridgeIndexerCore\Index\DataFilter;
use Divante\VsbridgeIndexerCatalog\Api\ArrayConverter\Product\CustomOptionConverterInterface;

/**
 * Class CustomOptionConverter
 */
class CustomOptionConverter implements CustomOptionConverterInterface
{
    /**
     * @var array
     */
    private $fieldsToDelete = [
        'default_title',
        'store_title',
        'default_price',
        'default_price_type',
        'store_price',
        'store_price_type',
        'product_id',
    ];

    /**
     * @var DataFilter
     */
    private $dataFilter;

    /**
     * CustomOptionConverter constructor.
     *
     * @param DataFilter $dataFilter
     */
    public function __construct(DataFilter $dataFilter)
    {
        $this->dataFilter = $dataFilter;
    }

    /**
     * @param array $options
     * @param array $optionValues
     *
     * @return array
     */
    public function process(array $options, array $optionValues): array
    {
        $groupOption = [];

        foreach ($optionValues as $optionValue) {
            $optionId = $optionValue['option_id'];
            $optionValue = $this->prepareValue($optionValue);
            $options[$optionId]['values'][] = $optionValue;
        }

        foreach ($options as $option) {
            $productId = $option['product_id'];
            $option = $this->prepareOption($option);
            $groupOption[$productId][] = $option;
        }

        return $groupOption;
    }

    /**
     * @param array $option
     *
     * @return array
     */
    private function prepareValue(array $option): array
    {
        $option = $this->unsetFields($option);
        unset($option['option_id']);

        return $option;
    }

    /**
     * @param array $option
     *
     * @return array
     */
    private function unsetFields(array $option): array
    {
        $option = $this->dataFilter->execute($option, $this->fieldsToDelete);

        if (isset($option['sku']) !== true) {
            unset($option['sku']);
        }

        if (isset($option['file_extension']) !== true) {
            unset($option['file_extension']);
        }

        return $option;
    }

    /**
     * @param array $option
     *
     * @return array
     */
    private function prepareOption(array $option): array
    {
        $option = $this->unsetFields($option);

        if ('drop_down' === $option['type']) {
            $option['type'] = 'select';
        }

        return $option;
    }
}
