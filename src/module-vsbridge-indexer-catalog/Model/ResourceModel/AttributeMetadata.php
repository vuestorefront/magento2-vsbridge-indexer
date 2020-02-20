<?php

declare(strict_types = 1);

/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel;

use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\AttributesMetadata\GetAttributeFields;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\LoadAttributes;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\Source\Table as SourceTable;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as OptionCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * Class AttributeMetadata
 */
class AttributeMetadata
{

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var OptionCollectionFactory
     */
    private $optionCollectionFactory;

    /**
     * @var GetAttributeFields
     */
    private $getAttributeFields;

    /**
     * @var LoadAttributes
     */
    private $loadAttributes;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var array
     */
    private $optionsByStore = [];

    /**
     * @var array
     */
    private $labelByAttribute = [];

    /**
     * AttributeMetadata constructor.
     *
     * @param ResourceConnection $resource
     * @param LoadAttributes $loadAttributes
     * @param GetAttributeFields $getAttributeFields
     * @param OptionCollectionFactory $optionCollectionFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ResourceConnection $resource,
        LoadAttributes $loadAttributes,
        GetAttributeFields $getAttributeFields,
        OptionCollectionFactory $optionCollectionFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->collectionFactory = $collectionFactory;
        $this->getAttributeFields = $getAttributeFields;
        $this->loadAttributes = $loadAttributes;
        $this->optionCollectionFactory = $optionCollectionFactory;
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function getAttributes(int $storeId)
    {
        if ($this->attributes === null) {
            $connection = $this->resource->getConnection();
            $rows = $connection->fetchAll($this->createBaseLoadSelect());

            $this->attributes = [];

            foreach ($rows as $row) {
                $this->attributes[] = $this->getAttributeFields->execute($row);
            }
        }

        $this->loadOptions($this->attributes, $storeId);
        $this->loadStoreLabels($this->attributes, $storeId);

        return $this->attributes;
    }

    /**
     * @param int $attributeId
     * @param int $storeId
     *
     * @return array
     */
    public function getOptions(int $attributeId, int $storeId): array
    {
        return $this->optionsByStore[$storeId][$attributeId] ?? [];
    }

    /**
     * @param int $attributeId
     * @param int $storeId
     *
     * @return string
     */
    public function getStoreLabels(int $attributeId, int $storeId): string
    {
        return $this->labelByAttribute[$storeId][$attributeId] ?? '';
    }

    /**
     * @param array $attributes
     * @param int $storeId
     *
     * @return void
     */
    private function loadOptions(array $attributes, int $storeId)
    {
        if (!isset($this->optionsByStore[$storeId])) {
            $attributeIds = array_column($attributes, 'attribute_id');

            $optionCollection = $this->optionCollectionFactory->create();
            $optionCollection->setStoreFilter($storeId);
            $optionCollection->addFieldToFilter('attribute_id', ['in' => $attributeIds]);
            $optionCollection->setOrder('sort_order', 'asc');

            $data = $this->resource->getConnection()->fetchAll($optionCollection->getSelect());
            $options = [];

            foreach ($data as $line) {
                $options[$line['attribute_id']][$line['option_id']] = [
                    'value' => $line['option_id'],
                    'label' => $line['value'],
                    'sort_order' => intval($line['sort_order']),
                ];
            }

            $this->optionsByStore[$storeId] = $options;

            $this->loadSourceModelOptions($attributes, $storeId);
        }
    }

    /**
     * @param array $attributes
     * @param int $storeId
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function loadSourceModelOptions(array $attributes, int $storeId)
    {
        foreach ($attributes as $attribute) {
            if ($this->useSourceModel($attribute)) {
                $attributeCode = $attribute['attribute_code'];
                $attributeId = $attribute['attribute_id'];
                $attribute = $this->loadAttributes->getAttributeByCode($attributeCode);
                $allOptions = [];

                foreach ($attribute->getSource()->getAllOptions() as $option) {
                    $allOptions[$option['value']] = [
                        'label' => (string)$option['label'],
                        'value' => (string)$option['value'],
                    ];
                }

                $this->optionsByStore[$storeId][$attributeId] = $allOptions;
            }
        }
    }

    /**
     * @param array $attribute
     *
     * @return bool
     */
    private function useSourceModel(array $attribute)
    {
        $source = $attribute['source_model'];

        if (!empty($source) && $source !== SourceTable::class) {
            return true;
        }

        return false;
    }

    /**
     * @param array $attributes
     * @param int $storeId
     *
     * @return void
     */
    private function loadStoreLabels(array $attributes, int $storeId)
    {
        if (!isset($this->labelByAttribute[$storeId])) {
            $attributeIds = array_column($attributes, 'attribute_id');
            $connection = $this->resource->getConnection();

            $bind = [':store_id' => $storeId];

            $select = $connection->select()->from(
                $this->resource->getTableName('eav_attribute_label'),
                [
                    'attribute_id',
                    'value',
                ]
            )->where('store_id = :store_id');

            $select->where($connection->prepareSqlCondition('attribute_id', ['in' => $attributeIds]));

            $labels = $connection->fetchAll($select, $bind);

            foreach ($labels as $label) {
                $this->labelByAttribute[$storeId][$label['attribute_id']] = $label['value'];
            }
        }
    }

    /**
     * @return Select
     */
    private function createBaseLoadSelect(): Select
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            [
                'is_visible',
                'is_visible_on_front',
            ],
            [
                ['eq' => 1],
                ['eq' => 1],
            ]
        );

        return $collection->getSelect();
    }
}
