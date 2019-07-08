<?php
/**
 * @package  Divante\VsbridgeIndexerCore
 * @author Joel Rainwater <joel.rain2o@gmail.com>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Model\Config\Source;

/**
 * Class IndexIdentifier
 */
class IndexIdentifier implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'id', 'label' => __('Store ID')],
            ['value' => 'code', 'label' => __('Store Code')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return ['id' => __('Store ID'), 'code' => __('Store Code')];
    }
}
