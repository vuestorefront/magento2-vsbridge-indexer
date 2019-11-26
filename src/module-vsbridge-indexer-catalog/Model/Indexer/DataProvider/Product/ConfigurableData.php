<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product;

use Divante\VsbridgeIndexerCatalog\Api\LoadInventoryInterface;
use Divante\VsbridgeIndexerCatalog\Model\ConfigurableProcessor\GetConfigurableOptions;
use Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product\Configurable\ChildAttributesProcessor;
use Divante\VsbridgeIndexerCatalog\Model\InventoryProcessor;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Configurable as ConfigurableResource;
use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeIndexerCore\Indexer\DataFilter;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;

/**
 * Class ConfigurableData
 */
class ConfigurableData implements DataProviderInterface
{
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
     * @var LoadInventoryInterface
     */
    private $loadInventory;

    /**
     * @var InventoryProcessor
     */
    private $inventoryProcessor;

    /**
     * @var ChildAttributesProcessor
     */
    private $childrenAttributeProcessor;

    /**
     * @var GetConfigurableOptions
     */
    private $configurableProcessor;

    /**
     * ConfigurableData constructor.
     *
     * @param DataFilter $dataFilter
     * @param ConfigurableResource $configurableResource
     * @param LoadInventoryInterface $loadInventory
     * @param GetConfigurableOptions $configurableProcessor
     * @param ChildAttributesProcessor $childrenAttributeProcessor
     * @param InventoryProcessor $inventoryProcessor
     */
    public function __construct(
        DataFilter $dataFilter,
        ConfigurableResource $configurableResource,
        LoadInventoryInterface $loadInventory,
        GetConfigurableOptions $configurableProcessor,
        ChildAttributesProcessor $childrenAttributeProcessor,
        InventoryProcessor $inventoryProcessor
    ) {
        $this->dataFilter = $dataFilter;
        $this->configurableResource = $configurableResource;
        $this->loadInventory = $loadInventory;
        $this->inventoryProcessor = $inventoryProcessor;
        $this->childrenAttributeProcessor = $childrenAttributeProcessor;
        $this->configurableProcessor = $configurableProcessor;
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $this->configurableResource->clear();
        $this->configurableResource->setProducts($indexData);
        $indexData = $this->prepareConfigurableChildrenAttributes($indexData, $storeId);

        $productsList = [];

        foreach ($indexData as $productId => $productDTO) {
            if (!isset($productDTO['configurable_children'])) {
                $productDTO['configurable_children'] = [];

                if (ConfigurableType::TYPE_CODE !== $productDTO['type_id']) {
                    $productsList[$productId] = $productDTO;
                }
                continue;
            }

            $productDTO = $this->applyConfigurableOptions($productDTO, $storeId);

            /**
             * Skip exporting configurable products without options
             */
            if (!empty($productDTO['configurable_options'])) {
                $productsList[$productId] = $this->prepareConfigurableProduct($productDTO);
            }
        }

        $this->configurableResource->clear();

        return $productsList;
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

        $allChildren = $this->childrenAttributeProcessor
            ->loadChildrenRawAttributesInBatches($storeId, $allChildren, $configurableAttributeCodes);

        foreach ($allChildren as $child) {
            $childId = $child['entity_id'];
            $child['id'] = (int) $childId;
            $parentIds = $child['parent_ids'];

            if (!isset($child['regular_price']) && isset($child['price'])) {
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
     * Apply attributes to product variants + extra options for products necessary for vsf
     * @param array $productDTO
     * @param int $storeId
     *
     * @return array
     * @throws \Exception
     */
    private function applyConfigurableOptions(array $productDTO, $storeId)
    {
        $configurableChildren = $productDTO['configurable_children'];
        $productAttributeOptions =
            $this->configurableResource->getProductConfigurableAttributes($productDTO);

        $productDTO['configurable_children'] = $configurableChildren;

        foreach ($productAttributeOptions as $productAttribute) {
            $attributeCode = $productAttribute['attribute_code'];

            if (!isset($productDTO[$attributeCode . '_options'])) {
                $productDTO[$attributeCode . '_options'] = [];
            }

            $options = $this->configurableProcessor->execute(
                $attributeCode,
                $storeId,
                $configurableChildren
            );

            $values = [];

            foreach ($options as $option) {
                $values[] = (int)$option['value'];
                $optionValue = [
                    'value_index' => $option['value'],
                    'label' => $option['label'],
                ];

                if (isset($option['swatch'])) {
                    $optionValue['swatch'] = $option['swatch'];
                }

                $productAttribute['values'][] = $optionValue;
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
        $finalPrice = $childPrice = [];
        $hasPrice = $this->hasPrice($productDTO);

        foreach ($configurableChildren as $child) {
            if ($child['stock']['is_in_stock']) {
                $areChildInStock = 1;
            }

            $childPrice[] = $child['price'];
            $finalPrice[] = $child['final_price'] ?? $child['final_price'] ?? $child['price'];
        }

        if (!$hasPrice && !empty($childPrice)) {
            $minPrice = min($childPrice);
            $productDTO['price'] = $minPrice;
            $productDTO['final_price'] = min($finalPrice);
            $productDTO['regular_price'] = $minPrice;
        }

        $isInStock = $productDTO['stock']['is_in_stock'];

        if (!$isInStock || !$areChildInStock) {
            $productDTO['stock']['is_in_stock'] = false;
            $productDTO['stock']['stock_status'] = 0;
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
     * @param array $productData
     *
     * @return array
     */
    private function filterData(array $productData)
    {
        return $this->dataFilter->execute($productData, $this->childBlackListConfig);
    }
}
