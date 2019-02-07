<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Config\Source;

use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use \Magento\Framework\Option\ArrayInterface;

/**
 * Class ProductType
 */
class ProductType implements ArrayInterface
{
    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    private $config;

    /**
     * @var
     */
    private $types;

    /**
     * ProductType constructor.
     *
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->getTypes() as $typeId => $type) {
            $options[] = [
                'value' => $typeId,
                'label' => (string)$type['label']
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    private function getTypes()
    {
        if ($this->types === null) {
            $productTypes = $this->config->getAll();

            foreach ($productTypes as $productTypeKey => $productTypeConfig) {
                $productTypes[$productTypeKey]['label'] = __($productTypeConfig['label']);
            }

            $this->types = $productTypes;
        }

        return $this->types;
    }
}
