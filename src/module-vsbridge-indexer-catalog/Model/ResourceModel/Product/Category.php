<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

/**
 * Class Category
 */
class Category
{

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var array Local cache for category names
     */
    private $categoryNameCache = [];

    /**
     * Category constructor.
     *
     * @param ResourceConnection $resourceModel
     * @param CategoryCollectionFactory $categoryCollectionFactory
     */
    public function __construct(ResourceConnection $resourceModel, CategoryCollectionFactory $categoryCollectionFactory)
    {
        $this->resource = $resourceModel;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * @param int $storeId
     * @param array $productIds
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadCategoryData($storeId, array $productIds)
    {
        $select       = $this->getCategoryProductSelect($productIds);
        $categoryData = $this->getConnection()->fetchAll($select);

        $categoryIds = [];

        foreach ($categoryData as $categoryDataRow) {
            $categoryIds[] = $categoryDataRow['category_id'];
        }

        $storeCategoryName = $this->loadCategoryNames(array_unique($categoryIds), $storeId);

        foreach ($categoryData as &$categoryDataRow) {
            $categoryDataRow['name'] = '';
            if (isset($storeCategoryName[(int)$categoryDataRow['category_id']])) {
                $categoryDataRow['name'] = $storeCategoryName[(int)$categoryDataRow['category_id']];
            }
        }

        return $categoryData;
    }

    /**
     * @param array $categoryIds
     * @param int $storeId
     *
     * @return array|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function loadCategoryNames(array $categoryIds, $storeId)
    {
        $loadCategoryIds = $categoryIds;

        if (isset($this->categoryNameCache[$storeId])) {
            $loadCategoryIds = array_diff($categoryIds, array_keys($this->categoryNameCache[$storeId]));
        }

        $loadCategoryIds  = array_map('intval', $loadCategoryIds);

        if (!empty($loadCategoryIds)) {
            $select = $this->prepareCategoryNameSelect($loadCategoryIds, $storeId);

            foreach ($this->getConnection()->fetchAll($select) as $row) {
                $categoryId = (int) $row['entity_id'];
                $this->categoryNameCache[$storeId][$categoryId] = $row['name'];
            }
        }

        return isset($this->categoryNameCache[$storeId]) ? $this->categoryNameCache[$storeId] : [];
    }

    /**
     * @param array $loadCategoryIds
     * @param int $storeId
     *
     * @return Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function prepareCategoryNameSelect(array $loadCategoryIds, $storeId)
    {
        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection->setStoreId($storeId);
        $categoryCollection->setStore($storeId);
        $categoryCollection->addFieldToFilter('entity_id', ['in' => $loadCategoryIds]);
        $categoryCollection->joinAttribute('name', 'catalog_category/name', 'entity_id');

        $select = $categoryCollection->getSelect();

        return $select;
    }

    /**
     * @param array $productIds
     *
     * @return Select
     */
    private function getCategoryProductSelect($productIds)
    {
        $table = $this->resource->getTableName('catalog_category_product');

        $select = $this->getConnection()->select()
            ->from(['cpi' => $table])
            ->where('cpi.product_id IN(?)', $productIds);

        return $select;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
}
