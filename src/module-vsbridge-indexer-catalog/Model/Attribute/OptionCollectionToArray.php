<?php

declare(strict_types = 1);

/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Attribute;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection as OptionCollection;

/**
 * Class OptionCollectionToArray
 */
class OptionCollectionToArray
{
    /**
     * @param OptionCollection $collection
     * @param array $additional
     *
     * @return array
     */
    public function execute(OptionCollection $collection, array $additional = []): array
    {
        $res = [];
        $additional['value'] = 'option_id';
        $additional['label'] = 'value';
        $additional['sort_order'] = 'sort_order';

        foreach ($collection as $item) {
            $data = [];

            foreach ($additional as $code => $field) {
                $value = $item->getData($field);

                if ('sort_order' === $field) {
                    $value = (int)$value;
                }

                if ('option_id' === $field) {
                    $value = (string)$value;
                }

                if ('swatch' === $field) {
                    $value = [
                        'type' => (int)$item->getData('swatch_type'),
                        'value' => $item->getData('swatch_value')
                    ];
                }

                $data[$code] = $value;
            }

            if ($data) {
                $res[] = $data;
            }
        }

        return $res;
    }
}
