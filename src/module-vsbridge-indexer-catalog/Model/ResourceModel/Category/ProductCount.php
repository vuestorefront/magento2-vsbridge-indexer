<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category;

use Magento\Framework\App\ResourceConnection;

/**
 * Class ProductCount
 */
class ProductCount
{

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var array
     */
    private $categoryProductCountCache = [];

    /**
     * Category constructor.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resource = $resourceConnection;
    }

    /**
     * @param array $categoryIds
     *
     * @return array
     */
    public function loadProductCount(array $categoryIds)
    {
        if (null === $this->categoryProductCountCache) {
            $this->categoryProductCountCache = [];
        }

        $loadCategoryIds = $categoryIds;

        if (!empty($this->categoryProductCountCache)) {
            $loadCategoryIds = array_diff($categoryIds, array_keys($this->categoryProductCountCache));
        }

        $loadCategoryIds = array_map('intval', $loadCategoryIds);

        if (!empty($loadCategoryIds)) {
            $result = $this->getProductCount($loadCategoryIds);

            foreach ($loadCategoryIds as $categoryId) {
                $categoryId = (int)$categoryId;
                $this->categoryProductCountCache[$categoryId] = 0;

                if (isset($result[$categoryId])) {
                    $this->categoryProductCountCache[$categoryId] = (int)$result[$categoryId];
                }
            }
        }

        return $this->categoryProductCountCache;
    }

    /**
     * @param array $categoryIds
     *
     * @return array
     */
    public function getProductCount(array $categoryIds)
    {
        $productTable = $this->resource->getTableName('catalog_category_product');

        $select = $this->getConnection()->select()->from(
            ['main_table' => $productTable],
            [
                'category_id',
                new \Zend_Db_Expr('COUNT(main_table.product_id)')
            ]
        )->where('main_table.category_id in (?)', $categoryIds);

        $select->group('main_table.category_id');

        return $this->getConnection()->fetchPairs($select);
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
}
