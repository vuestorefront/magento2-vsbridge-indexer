<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product;

use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeIndexerCatalog\Model\Attributes\ConfigurableAttributes;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\AttributeDataProvider;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Configurable as ConfigurableResource;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Inventory as InventoryResource;
use Divante\VsbridgeIndexerCore\Index\Mapping\GeneralMapping;
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
     * @var GeneralMapping
     */
    private $generalMapping;

    /**
     * @var ConfigurableAttributes
     */
    private $configurableAttributes;

    /**
     * ConfigurableData constructor.
     *
     * @param DataFilter $dataFilter
     * @param ConfigurableResource $configurableResource
     * @param AttributeDataProvider $attributeResource
     * @param InventoryResource $inventoryResource
     * @param ConfigurableAttributes $configurableAttributes
     * @param GeneralMapping $generalMapping
     */
    public function __construct(
        DataFilter $dataFilter,
        ConfigurableResource $configurableResource,
        AttributeDataProvider $attributeResource,
        InventoryResource $inventoryResource,
        ConfigurableAttributes $configurableAttributes,
        GeneralMapping $generalMapping
    ) {
        $this->dataFilter = $dataFilter;
        $this->configurableResource = $configurableResource;
        $this->resourceAttributeModel = $attributeResource;
        $this->inventoryResource = $inventoryResource;
        $this->generalMapping = $generalMapping;
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

        //$notifyStockDefaultValue = $this->getNotifyForQtyBelowDefaultValue($storeId);
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
                $productStockData = $this->generalMapping->prepareStockData($productStockData);
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
                $value = $child[$attributeCode];

                if (isset($value)) {
                    $values[] = (int) $value;
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
     * @param int $storeId
     * @param array $allChildren
     * @param array $requiredAttributes
     *
     * @return array
     */
    private function loadChildrenRawAttributesInBatches($storeId, array $allChildren, array $requiredAttributes)
    {
        $requiredAttribute = array_unique($requiredAttributes);
        $childIds = [];

        foreach ($allChildren as $child) {
            $childIds[] = $child['entity_id'];

            if (count($childIds) >= $this->batchSize) {
                $attributeData = $this->resourceAttributeModel->loadAttributesData(
                    $storeId,
                    $childIds,
                    $requiredAttribute
                );

                foreach ($attributeData as $productId => $attribute) {
                    $allChildren[$productId] = array_merge(
                        $allChildren[$productId],
                        $attribute
                    );
                }

                $childIds = [];
                $attributeData = null;
            }
        }

        if (count($childIds)) {
            $attributeData = $this->resourceAttributeModel->loadAttributesData(
                $storeId,
                $childIds,
                $requiredAttribute
            );

            foreach ($attributeData as $productId => $attribute) {
                $allChildren[$productId] = array_merge(
                    $allChildren[$productId],
                    $attribute
                );
            }

            $childIds = null;
            $attributeData = null;
        }

        return $allChildren;
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
