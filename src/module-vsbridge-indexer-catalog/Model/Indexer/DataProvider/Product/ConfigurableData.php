<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product;

use Divante\VsbridgeIndexerCatalog\Model\Attributes\ConfigurableAttributes;
use Divante\VsbridgeIndexerCatalog\Model\InventoryProcessor;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\AttributeDataProvider;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Configurable as ConfigurableResource;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Inventory as InventoryResource;
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
     * @var InventoryResource
     */
    private $inventoryResource;

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
     * ConfigurableData constructor.
     *
     * @param DataFilter $dataFilter
     * @param ConfigurableResource $configurableResource
     * @param AttributeDataProvider $attributeResource
     * @param InventoryResource $inventoryResource
     * @param ConfigurableAttributes $configurableAttributes
     * @param TierPriceProcessor $tierPriceProcessor
     * @param InventoryProcessor $inventoryProcessor
     */
    public function __construct(
        DataFilter $dataFilter,
        ConfigurableResource $configurableResource,
        AttributeDataProvider $attributeResource,
        InventoryResource $inventoryResource,
        ConfigurableAttributes $configurableAttributes,
        TierPriceProcessor $tierPriceProcessor,
        InventoryProcessor $inventoryProcessor
    ) {
        $this->dataFilter = $dataFilter;
        $this->configurableResource = $configurableResource;
        $this->resourceAttributeModel = $attributeResource;
        $this->inventoryResource = $inventoryResource;
        $this->inventoryProcessor = $inventoryProcessor;
        $this->tierPriceProcessor = $tierPriceProcessor;
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

            $productDTO = $this->applyConfigurableOptions($productDTO);
            $indexData[$productId]  = $this->updateStockStatus($productDTO);
        }

        $this->configurableResource->clear();

        return $indexData;
    }

    /**
     * @param array $indexData
     * @param $storeId
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

        $childIds = array_keys($allChildren);

        $stockRowData = $this->inventoryResource->loadChildrenData($storeId, $childIds);
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
     *
     * @return array
     * @throws \Exception
     */
    private function applyConfigurableOptions(array $productDTO)
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
                $productAttribute['values'][] = ['value_index' => $value];
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
    private function updateStockStatus(array $productDTO)
    {
        $configurableChildren = $productDTO['configurable_children'];
        $areChildInStock = 0;

        foreach ($configurableChildren as $child) {
            if ($child['stock']['is_in_stock']) {
                $areChildInStock = 1;
                break;
            }
        }

        $productStockStatus = $productDTO['stock']['stock_status'];
        $isInStock = $productDTO['stock']['is_in_stock'];

        if (!$isInStock || ($productStockStatus && !$areChildInStock)) {
            $productDTO['stock']['stock_status'] = 0;
        }

        return $productDTO;
    }

    /**
     * @param $storeId
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
     * @param $size
     *
     * @return \Generator
     */
    private function getChildrenInBatches(array $documents, $size)
    {
        $i = 0;
        $batch = [];

        foreach ($documents as $documentName => $documentValue) {
            $batch[$documentName] = $documentValue;

            if (++$i == $size) {
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
