<?php

declare(strict_types = 1);

/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Attribute;

use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\AttributeDataProvider;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

/**
 * Class LoadOptionLabelById
 */
class LoadOptionLabelById
{
    /**
     * @var AttributeDataProvider
     */
    private $attributeDataProvider;

    /**
     * @var array
     */
    private $optionsByAttribute = [];

    /**
     * LoadLabelByOptionId constructor.
     *
     * @param AttributeDataProvider $attributeDataProvider
     */
    public function __construct(AttributeDataProvider $attributeDataProvider)
    {
        $this->attributeDataProvider = $attributeDataProvider;
    }

    /**
     * @param string $attributeCode
     * @param int $optionId
     * @param int $storeId
     *
     * @return string
     */
    public function execute(string $attributeCode, int $optionId, int $storeId): string
    {
        $attributeModel = $this->attributeDataProvider->getAttributeByCode($attributeCode);
        $attributeModel->setStoreId($storeId);
        $options = $this->loadOptions($attributeModel);

        foreach ($options as $option) {
            if ($optionId === (int)$option['value']) {
                return $option['label'];
            }
        }

        return '';
    }

    /**
     * @param Attribute $attribute
     *
     * @return mixed
     */
    private function loadOptions(Attribute $attribute)
    {
        $key = $attribute->getId() . '_' . $attribute->getStoreId();

        if (!isset($this->optionsByAttribute[$key])) {
            $this->optionsByAttribute[$key] = $attribute->getOptions();
        }

        return $this->optionsByAttribute[$key];
    }
}
