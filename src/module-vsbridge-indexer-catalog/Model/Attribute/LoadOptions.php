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
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection as OptionCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;

use Magento\Eav\Model\Entity\Attribute\Source\Table as SourceTable;

/**
 * Class LoadOptionLabelById
 */
class LoadOptions
{
    /**
     * @var AttributeDataProvider
     */
    private $attributeDataProvider;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var OptionCollectionToArray
     */
    private $optionCollectionToArray;

    /**
     * @var array
     */
    private $optionsByAttribute = [];

    /**
     * LoadOptions constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param OptionCollectionToArray $optionCollectionToArray
     * @param AttributeDataProvider $attributeDataProvider
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        OptionCollectionToArray $optionCollectionToArray,
        AttributeDataProvider $attributeDataProvider
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->attributeDataProvider = $attributeDataProvider;
        $this->optionCollectionToArray = $optionCollectionToArray;
    }

    /**
     * @param string $attributeCode
     * @param int $storeId
     *
     * @return string
     */
    public function execute(string $attributeCode, int $storeId): array
    {
        $attributeModel = $this->attributeDataProvider->getAttributeByCode($attributeCode);
        $attributeModel->setStoreId($storeId);

        return $this->loadOptions($attributeModel);
    }

    /**
     * @param Attribute $attribute
     *
     * @return array
     */
    private function loadOptions(Attribute $attribute): array
    {
        $key = $attribute->getId() . '_' . $attribute->getStoreId();

        if (!isset($this->optionsByAttribute[$key])) {
            $source = $attribute->getSource();

            if (SourceTable::class !== get_class($source) &&
                $source instanceof \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
            ) {
                $options = $source->getAllOptions();
            } else {
                $attributeId = $attribute->getAttributeId();
                $storeId = $attribute->getStoreId();

                /** @var OptionCollection $options */
                $options = $this->collectionFactory->create();
                $options->setOrder('sort_order', 'asc');
                $options->setAttributeFilter($attributeId)->setStoreFilter($storeId);
                $options = $this->toOptionArray($options);
            }

            $this->optionsByAttribute[$key] = $options;
        }

        return $this->optionsByAttribute[$key];
    }

    /**
     * @param OptionCollection $collection
     *
     * @return array
     */
    private function toOptionArray(OptionCollection $collection): array
    {
        return $this->optionCollectionToArray->execute($collection);
    }
}
