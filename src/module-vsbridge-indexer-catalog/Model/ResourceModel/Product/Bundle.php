<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product;

use Divante\VsbridgeIndexerCatalog\Model\ProductMetaData;
use Magento\Catalog\Helper\Data;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Bundle
 */
class Bundle
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var array
     */
    private $products;

    /**
     * @var array
     */
    private $bundleProductIds;

    /**
     * @var array
     */
    private $bundleOptionsByProduct = [];

    /**
     * @var ProductMetaData
     */
    private $productMetaData;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Data
     */
    private $catalogHelper;

    /**
     * Bundle constructor.
     *
     * @param ProductMetaData $productMetaData
     * @param ResourceConnection $resourceModel
     * @param StoreManagerInterface $storeManager
     * @param Data $catalogHelper
     */
    public function __construct(
        ProductMetaData $productMetaData,
        ResourceConnection $resourceModel,
        StoreManagerInterface $storeManager,
        Data $catalogHelper
    ) {
        $this->resource = $resourceModel;
        $this->productMetaData = $productMetaData;
        $this->storeManager = $storeManager;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * @param array $products
     *
     * @return void
     * @throws \Exception
     */
    public function setProducts(array $products)
    {
        $linkField = $this->productMetaData->get()->getLinkField();

        foreach ($products as $product) {
            $this->products[$product[$linkField]] = $product;
        }
    }

    /**
     * Clear data
     * @return void
     */
    public function clear()
    {
        $this->products = null;
        $this->bundleOptionsByProduct = [];
        $this->bundleProductIds = null;
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function loadBundleOptions($storeId)
    {
        $productIds = $this->getBundleIds();

        if (empty($productIds)) {
            return [];
        }

        $this->initOptions($storeId);
        $this->initSelection($storeId);

        return $this->bundleOptionsByProduct;
    }

    /**
     * Init Options
     *
     * @param int $storeId
     *
     * @return void
     */
    private function initOptions($storeId)
    {
        $bundleOptions = $this->getBundleOptionsFromResource($storeId);

        foreach ($bundleOptions as $bundleOption) {
            /* entity_id or row_id*/
            $parentId = $bundleOption['parent_id'];
            $parentEntityId = $this->products[$parentId]['entity_id'];
            $optionId = $bundleOption['option_id'];

            $this->bundleOptionsByProduct[$parentEntityId][$optionId] = [
                'option_id' => (int)($bundleOption['option_id']),
                'position' => (int)($bundleOption['position']),
                'type' => $bundleOption['type'],
                'sku' => $this->products[$parentId]['sku'],
                'title' => $bundleOption['title'],
                'required' => (bool)$bundleOption['required'],
            ];
        }
    }

    /**
     * Append Selection
     *
     * @param int $storeId
     *
     * @return void
     */
    private function initSelection(int $storeId)
    {
        $bundleSelections = $this->getBundleSelections($storeId);
        $simpleIds = array_column($bundleSelections, 'product_id');
        $simpleSkuList = $this->getProductSku($simpleIds);

        foreach ($bundleSelections as $selection) {
            $optionId = $selection['option_id'];
            /*row_id or entity_id*/
            $parentId = $selection['parent_product_id'];
            $entityId = $this->products[$parentId]['entity_id'];
            $productId = $selection['product_id'];
            $bundlePriceType = $this->products[$parentId]['price_type'];

            $selectionPriceType = $bundlePriceType ? $selection['selection_price_type'] : null;
            $selectionPrice = $bundlePriceType ? $selection['selection_price_value'] : null;

            $this->bundleOptionsByProduct[$entityId][$optionId]['product_links'][] = [
                'id' => (int)$selection['selection_id'],
                'is_default' => (bool)$selection['is_default'],
                'qty' => (float)$selection['selection_qty'],
                'can_change_quantity' => (bool)$selection['selection_can_change_qty'],
                'price' => (float)$selectionPrice,
                'price_type' => $selectionPriceType,
                'position' => (int)($selection['position']),
                'sku' => $simpleSkuList[$productId],
            ];
        }
    }

    /**
     * @return array
     */
    private function getBundleSelections($storeId)
    {
        $productIds = $this->getBundleIds();
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            ['selection' => $this->resource->getTableName('catalog_product_bundle_selection')]
        );
        $productIdColumn = 'parent_product_id';

        if (!$this->catalogHelper->isPriceGlobal()) {
            $websiteId = $this->storeManager->getStore($storeId)
                ->getWebsiteId();
            $priceType = $connection->getCheckSql(
                'price.selection_price_type IS NOT NULL',
                'price.selection_price_type',
                'selection.selection_price_type'
            );
            $priceValue = $connection->getCheckSql(
                'price.selection_price_value IS NOT NULL',
                'price.selection_price_value',
                'selection.selection_price_value'
            );
            $select->joinLeft(
                ['price' => $this->resource->getTableName('catalog_product_bundle_selection_price')],
                'selection.selection_id = price.selection_id AND price.website_id = ' . (int)$websiteId .
                ' AND selection.parent_product_id = price.parent_product_id',
                [
                    'selection_price_type' => $priceType,
                    'selection_price_value' => $priceValue,
                    'parent_product_id' => 'selection.parent_product_id'
                ]
            );
            $productIdColumn = 'selection.' . $productIdColumn;
        }

        $select->where("$productIdColumn IN (?)", $productIds);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @param array $productIds
     *
     * @return array
     */
    private function getProductSku(array $productIds)
    {
        $select = $this->getConnection()->select();
        $select->from($this->resource->getTableName('catalog_product_entity'), ['entity_id', 'sku']);
        $select->where('entity_id IN (?)', $productIds);

        return $this->getConnection()->fetchPairs($select);
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    private function getBundleOptionsFromResource($storeId)
    {
        $productIds = $this->getBundleIds();

        $select = $this->getConnection()->select()->from(
            ['main_table' => $this->resource->getTableName('catalog_product_bundle_option')]
        );

        $select->where('parent_id IN (?)', $productIds);
        $select->order('main_table.position asc')
            ->order('main_table.option_id asc');

        $select = $this->joinOptionValues($select, $storeId);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @param Select $select
     * @param int $storeId
     *
     * @return Select
     */
    private function joinOptionValues(Select $select, $storeId)
    {
        $select
            ->joinLeft(
                ['option_value_default' => $this->resource->getTableName('catalog_product_bundle_option_value')],
                'main_table.option_id = option_value_default.option_id and option_value_default.store_id = 0',
                []
            )
            ->columns(['default_title' => 'option_value_default.title']);

        $title = $this->getConnection()->getCheckSql(
            'option_value.title IS NOT NULL',
            'option_value.title',
            'option_value_default.title'
        );

        $select->columns(['title' => $title])
            ->joinLeft(
                ['option_value' => $this->resource->getTableName('catalog_product_bundle_option_value')],
                $this->getConnection()->quoteInto(
                    'main_table.option_id = option_value.option_id and option_value.store_id = ?',
                    $storeId
                ),
                []
            );

        return $select;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function getBundleIds()
    {
        if (null === $this->bundleProductIds) {
            $this->bundleProductIds = [];

            foreach ($this->products as $productData) {
                if ('bundle' === $productData['type_id']) {
                    $linkFieldId = $this->productMetaData->get()->getLinkField();
                    $this->bundleProductIds[] = $productData[$linkFieldId];
                }
            }
        }

        return $this->bundleProductIds;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
}
