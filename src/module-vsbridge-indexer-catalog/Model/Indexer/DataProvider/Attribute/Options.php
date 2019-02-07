<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Attribute;

use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Eav\Model\Entity\Attribute\Source\Table as SourceTable;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as EntityResource;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection as OptionCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Framework\Validator\UniversalFactory;

/**
 * Class Options
 */
class Options implements DataProviderInterface
{

    /**
     * @var UniversalFactory
     */
    private $universalFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    /**
     * @var EntityResource
     */
    private $entityAttributeResource;

    /**
     * Options constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param AttributeFactory $attributeFactory
     * @param UniversalFactory $universalFactory
     * @param EntityResource $entityResource
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        AttributeFactory $attributeFactory,
        UniversalFactory $universalFactory,
        EntityResource $entityResource
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->collectionFactory = $collectionFactory;
        $this->universalFactory = $universalFactory;
        $this->entityAttributeResource = $entityResource;
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        foreach ($indexData as $attributeId => $attributeData) {
            $attributeData['default_frontend_label'] = $attributeData['frontend_label'];
            $storeLabels = $this->getStoreLabelsByAttributeId($attributeId);

            if (isset($storeLabels[$storeId])) {
                $attributeData['frontend_label'] = $storeLabels[$storeId];
            }

            if ($this->useSource($attributeData)) {
                $attributeData['options'] = $this->getAttributeOptions($attributeData, $storeId);
            }

            $indexData[$attributeId] = $attributeData;
        }

        return $indexData;
    }

    /**
     * @param array $attributeData
     * @param int   $storeId
     *
     * @return array
     */
    public function getAttributeOptions(array $attributeData, $storeId)
    {
        $values = [];
        $source = (string)$attributeData['source_model'];
        $attributeId = $attributeData['attribute_id'];

        if ('' !== $source && SourceTable::class !== $source) {
            $sourceModel = $this->universalFactory->create($source);

            if (false !== $sourceModel) {
                if ($sourceModel instanceof \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource) {
                    ///** @var Attribute $attribute */
                    $attribute = $this->attributeFactory->create($attributeData);
                    $attribute->setStoreId($storeId);
                    $sourceModel->setAttribute($attribute);
                }

                $values = $sourceModel->getAllOptions(false);
            }
        } else {
            /** @var OptionCollection $options */
            $options = $this->collectionFactory->create();
            $options->setOrder('sort_order', 'asc');
            $options->setAttributeFilter($attributeId)->setStoreFilter($storeId);
            $values = $this->toOptionArray($options);
        }

        return $values;
    }

    /**
     * @param OptionCollection $collection
     *
     * @param array $additional
     *
     * @return array
     */
    public function toOptionArray(OptionCollection $collection, array $additional = [])
    {
        $res = [];
        $additional['value'] = 'option_id';
        $additional['label'] = 'value';
        $additional['sort_order'] = 'sort_order';

        foreach ($collection as $item) {
            $data = [];

            foreach ($additional as $code => $field) {
                $value = $item->getData($field);

                if ($field === 'sort_order') {
                    $value = (int)$value;
                }

                if ($field === 'option_id') {
                    $value = (string)$value;
                }

                $data[$code] = $value;
            }

            if ($data) {
                $res[] = $data;
            }
        }

        return $res;
    }

    /**
     * @param array $attributeData
     *
     * @return bool
     */
    private function useSource(array $attributeData)
    {
        return $attributeData['frontend_input'] === 'select' || $attributeData['frontend_input'] === 'multiselect'
               || $attributeData['source_model'] != '';
    }

    /**
     * @param int $attributeId
     *
     * @return array
     */
    private function getStoreLabelsByAttributeId($attributeId)
    {
        return $this->entityAttributeResource->getStoreLabelsByAttributeId($attributeId);
    }
}
