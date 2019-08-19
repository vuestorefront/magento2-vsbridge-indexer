<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product;

use Divante\VsbridgeIndexerCatalog\Model\Attribute\LoadOptionLabelById;
use Divante\VsbridgeIndexerCatalog\Model\Attributes\ConfigurableAttributes;
use Divante\VsbridgeIndexerCatalog\Model\InventoryProcessor;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\AttributeDataProvider;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Configurable as ConfigurableResource;
use Divante\VsbridgeIndexerCatalog\Api\LoadInventoryInterface;
use Divante\VsbridgeIndexerCatalog\Model\TierPriceProcessor;
use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeIndexerCore\Indexer\DataFilter;

/**
 * Class ConfigurableData
 */
class ConfigurableData implements DataProviderInterface
{

    /**
     * @var int
     */
    private $batchSize = 500;

    /**
     * @var array
     */
    private $childBlackListConfig = [
        'entity_id',
        'row_id',
        'type_id',
        'parent_id',
        'parent_ids',
    ];

    /**
     * @var DataFilter
     */
    private $dataFilter;

    /**
     * @var ConfigurableResource
     */
    private $configurableResource;

    /**
     * @var  AttributeDataProvider
     */
    private $resourceAttributeModel;

    /**
     * @var LoadInventoryInterface
     */
    private $loadInventory;

    /**
     * @var InventoryProcessor
     */
    private $inventoryProcessor;

    /**
     * @var ConfigurableAttributes
     */
    private $configurableAttributes;

    /**
     * @var TierPriceProcessor
     */
    private $tierPriceProcessor;

    /**
     * @var LoadOptionLabelById
     */
    private $loadOptionLabelById;

    /**
     * ConfigurableData constructor.
     *
     * @param DataFilter $dataFilter
     * @param ConfigurableResource $configurableResource
     * @param AttributeDataProvider $attributeResource
     * @param LoadInventoryInterface $loadInventory
     * @param LoadOptionLabelById $loadOptionLabelById
     * @param ConfigurableAttributes $configurableAttributes
     * @param TierPriceProcessor $tierPriceProcessor
     * @param InventoryProcessor $inventoryProcessor
     */
    public function __construct(
        DataFilter $dataFilter,
        ConfigurableResource $configurableResource,
        AttributeDataProvider $attributeResource,
        LoadInventoryInterface $loadInventory,
        LoadOptionLabelById $loadOptionLabelById,
        ConfigurableAttributes $configurableAttributes,
        TierPriceProcessor $tierPriceProcessor,
        InventoryProcessor $inventoryProcessor
    ) {
        $this->dataFilter = $dataFilter;
        $this->configurableResource = $configurableResource;
        $this->resourceAttributeModel = $attributeResource;
        $this->loadInventory = $loadInventory;
        $this->inventoryProcessor = $inventoryProcessor;
        $this->tierPriceProcessor = $tierPriceProcessor;
        $this->loadOptionLabelById = $loadOptionLabelById;
        $this->configurableAttributes = $configurableAttributes;
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $this->configurableResource->clear();
        $this->configurableResource->setProducts($indexData);
        $indexData = $this->prepareConfigurableChildrenAttributes($indexData, $storeId);

        foreach ($indexData as $productId => $productDTO) {
            if (!isset($productDTO['configurable_children'])) {
                $indexData[$productId]['configurable_children'] = [];
                continue;
            }

            $productDTO = $this->applyConfigurableOptions($productDTO, $storeId);
            $indexData[$productId]  = $this->prepareConfigurableProduct($productDTO);
        }

        $this->configurableResource->clear();

        return $indexData;
    }

    /**
     * @param array $indexData
     * @param int $storeId
     *
     * @return array
     * @throws \Exception
     */
    private function prepareConfigurableChildrenAttributes(array $indexData, $storeId)
    {
        $allChildren = $this->configurableResource->getSimpleProducts($storeId);

        if (null === $allChildren) {
            return $indexData;
        }

        $stockRowData = $this->loadInventory->execute($allChildren, $storeId);
        $configurableAttributeCodes = $this->configurableResource->getConfigurableAttributeCodes();

        $requiredAttributes = array_merge(
            $this->getRequiredChildrenAttributes(),
            $configurableAttributeCodes
        );

        $requiredAttribute = array_unique($requiredAttributes);
        $allChildren = $this->loadChildrenRawAttributesInBatches($storeId, $allChildren, $requiredAttribute);

        foreach ($allChildren as $child) {
            $childId = $child['entity_id'];
            $child['id'] = (int) $childId;
            $parentIds = $child['parent_ids'];

            // @TODO add support for final_price in configurable_children -> check if it really necessary. Probably not
            if (isset($child['price'])) {
                $child['regular_price'] = $child['price'];
            }

            if (isset($stockRowData[$childId])) {
                $productStockData = $stockRowData[$childId];

                unset($productStockData['product_id']);
                $productStockData = $this->inventoryProcessor->prepareInventoryData($storeId, $productStockData);
                $child['stock'] = $productStockData;
            }

            foreach ($parentIds as $parentId) {
                $child = $this->filterData($child);

                if (!isset($indexData[$parentId]['configurable_options'])) {
                    $indexData[$parentId]['configurable_options'] = [];
                }

                $indexData[$parentId]['configurable_children'][] = $child;
            }
        }

        $allChildren = null;

        return $indexData;
    }

    /**
     * @return array
     */
    private function getRequiredChildrenAttributes()
    {
        return $this->configurableAttributes->getChildrenRequiredAttributes();
    }

    /**
     * Apply attributes to product variants + extra options for products necessary for vsf
     * @param array $productDTO
     * @param $storeId
     *
     * @return array
     * @throws \Exception
     */
    private function applyConfigurableOptions(array $productDTO, $storeId)
    {
        $configurableChildren = $productDTO['configurable_children'];
        $productAttributeOptions =
            $this->configurableResource->getProductConfigurableAttributes($productDTO);

        foreach ($productAttributeOptions as $productAttribute) {
            $attributeCode = $productAttribute['attribute_code'];

            if (!isset($productDTO[$attributeCode . '_options'])) {
                $productDTO[$attributeCode . '_options'] = [];
            }

            $values = [];

            foreach ($configurableChildren as $child) {
                if (isset($child[$attributeCode])) {
                    $value = $child[$attributeCode];

                    if (isset($value)) {
                        $values[] = (int) $value;
                    }
                }
            }

            $productDTO['configurable_children'] = $configurableChildren;
            $values = array_values(array_unique($values));

            foreach ($values as $value) {
                $label = $this->loadOptionLabelById->execute($attributeCode, $value, $storeId);
                $productAttribute['values'][] = [
                    'value_index' => $value,
                    'label' => $label,
                ];
            }

            $productDTO['configurable_options'][] = $productAttribute;
            $productDTO[$productAttribute['attribute_code'] . '_options'] = $values;
        }

        return $productDTO;
    }

    /**
     * @param array $productDTO
     *
     * @return array
     */
    private function prepareConfigurableProduct(array $productDTO)
    {
        $configurableChildren = $productDTO['configurable_children'];
        $areChildInStock = 0;
        $childPrice = [];
        $hasPrice = $this->hasPrice($productDTO);

        foreach ($configurableChildren as $child) {
            if ($child['stock']['is_in_stock']) {
                $areChildInStock = 1;
            }

            $childPrice[] = $child['price'];
        }

        if (!$hasPrice && !empty($childPrice)) {
            $minPrice = min($childPrice);
            $productDTO['price'] = $minPrice;
            $productDTO['final_price'] = $minPrice;
            $productDTO['regular_price'] = $minPrice;
        }

        $productStockStatus = $productDTO['stock']['stock_status'];
        $isInStock = $productDTO['stock']['is_in_stock'];

        if (!$isInStock || ($productStockStatus && !$areChildInStock)) {
            $productDTO['stock']['stock_status'] = 0;
        }

        if (!$areChildInStock) {
            $productDTO['stock']['is_in_stock'] = false;
            $productDTO['visibility'] = 1;
        }

        return $productDTO;
    }

    /**
     * @param array $product
     *
     * @return bool
     */
    private function hasPrice(array $product)
    {
        $priceFields = [
            'price',
            'final_price',
        ];

        foreach ($priceFields as $field) {
            if (!isset($product[$field])) {
                return false;
            }

            if (0 === (int)$product[$field]) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param int $storeId
     * @param array $allChildren
     * @param array $requiredAttributes
     *
     * @return array
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function loadChildrenRawAttributesInBatches($storeId, array $allChildren, array $requiredAttributes)
    {
        $requiredAttribute = array_unique($requiredAttributes);

        foreach ($this->getChildrenInBatches($allChildren, $this->batchSize) as $batch) {
            $childIds = array_keys($batch);
            $allAttributesData = $this->resourceAttributeModel->loadAttributesData(
                $storeId,
                $childIds,
                $requiredAttribute
            );

            foreach ($allAttributesData as $productId => $attributes) {
                if ($this->tierPriceProcessor->syncTierPrices()) {
                    /*we need some extra attributes to apply tier prices*/
                    $batch[$productId] = array_merge(
                        $allChildren[$productId],
                        $attributes
                    );
                } else {
                    $allChildren[$productId] = array_merge(
                        $allChildren[$productId],
                        $attributes
                    );
                }
            }

            if ($this->tierPriceProcessor->syncTierPrices()) {
                $batch = $this->tierPriceProcessor->applyTierGroupPrices($batch, $storeId);
                $allChildren = array_replace_recursive($allChildren, $batch);
            }
        }

        return $allChildren;
    }

    /**
     * @param array $documents
     * @param int $batchSize
     *
     * @return \Generator
     */
    private function getChildrenInBatches(array $documents, $batchSize)
    {
        $i = 0;
        $batch = [];

        foreach ($documents as $documentName => $documentValue) {
            $batch[$documentName] = $documentValue;

            if (++$i == $batchSize) {
                yield $batch;
                $i = 0;
                $batch = [];
            }
        }

        if (count($batch) > 0) {
            yield $batch;
        }
    }

    /**
     * @param array $productData
     *
     * @return array
     */
    private function filterData(array $productData)
    {
        return $this->dataFilter->execute($productData, $this->childBlackListConfig);
    }
}
