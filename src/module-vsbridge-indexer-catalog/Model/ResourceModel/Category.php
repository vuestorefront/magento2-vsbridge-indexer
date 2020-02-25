<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel;

use Divante\VsbridgeIndexerCatalog\Model\CategoryMetaData;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category\BaseSelectModifierInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Category as CoreCategoryModel;
use Magento\Framework\DB\Select;

/**
 * Class Category
 */
class Category
{
    /**
     * Alias form category entity table
     */
    const MAIN_TABLE_ALIAS = 'entity';

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var BaseSelectModifierInterface
     */
    private $baseSelectModifier;

    /**
     * @var CategoryMetaData
     */
    private $categoryMetaData;

    /**
     * Category constructor.
     *
     * @param BaseSelectModifierInterface $baseSelectModifier
     * @param ResourceConnection $resourceConnection
     * @param CategoryMetaData $categoryMetaData
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        BaseSelectModifierInterface $baseSelectModifier,
        ResourceConnection $resourceConnection,
        CategoryMetaData $categoryMetaData,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resourceConnection;
        $this->categoryMetaData = $categoryMetaData;
        $this->storeManager = $storeManager;
        $this->baseSelectModifier = $baseSelectModifier;
    }

    /**
     * @param int $storeId
     * @param array $categoryIds
     * @param int $fromId
     * @param int $limit
     *
     * @return array
     * @throws \Exception
     */
    public function getCategories($storeId = 1, array $categoryIds = [], $fromId = 0, $limit = 1000)
    {
        $select = $this->filterByStore($storeId);
        $tableName = self::MAIN_TABLE_ALIAS;

        if (!empty($categoryIds)) {
            $select->where(sprintf("%s.entity_id IN (?)", $tableName), $categoryIds);
        }

        $select->where(sprintf("%s.entity_id > ?", $tableName), $fromId);
        $select->limit($limit);
        $select->order(sprintf("%s.entity_id ASC", $tableName));

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @param int $storeId
     * @param array $productIds
     *
     * @return array
     * @throws \Exception
     */
    public function getCategoryProductSelect($storeId, array $productIds)
    {
        $select = $this->filterByStore($storeId);
        $table = $this->resource->getTableName('catalog_category_product');
        $entityIdField = $this->categoryMetaData->get()->getIdentifierField();
        $select->reset(Select::COLUMNS);
        $select->joinInner(
            ['cpi' => $table],
            self::MAIN_TABLE_ALIAS . ".$entityIdField = cpi.category_id",
            [
                'category_id',
                'product_id',
                'position',
            ]
        )->where('cpi.product_id IN (?)', $productIds);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @param array $categoryIds
     *
     * @return array
     * @throws \Exception
     */
    public function getParentIds(array $categoryIds)
    {
        $metaData = $this->categoryMetaData->get();
        $entityField = $metaData->getIdentifierField();

        $select = $this->getConnection()->select()->from(
            [self::MAIN_TABLE_ALIAS => $metaData->getEntityTable()],
            ['path']
        );

        $select->where(
            "$entityField IN (?)",
            array_map('intval', $categoryIds)
        );

        $paths = $this->getConnection()->fetchCol($select);
        $parentIds = [];

        foreach ($paths as $path) {
            $path = explode('/', $path);

            foreach ($path as $catId) {
                $catId = (int)$catId;

                if ($catId !== CoreCategoryModel::TREE_ROOT_ID) {
                    $parentIds[] = $catId;
                }
            }
        }

        return array_unique($parentIds);
    }

    /**
     * @param int $categoryId
     *
     * @return int[]
     * @throws \Exception
     */
    public function getAllSubCategories(int $categoryId): array
    {
        $metaData = $this->categoryMetaData->get();
        $entityField = $metaData->getIdentifierField();
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            [self::MAIN_TABLE_ALIAS => $metaData->getEntityTable()],
            [$entityField]
        );

        $catIdExpr = $connection->quote("%/{$categoryId}/%");
        $select->where("path like {$catIdExpr}");

        return $connection->fetchCol($select);
    }

    /**
     * @param int $storeId
     *
     * @return \Magento\Framework\DB\Select
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function filterByStore($storeId)
    {
        $metaData = $this->categoryMetaData->get();
        $select = $this->getConnection()->select()->from(
            [self::MAIN_TABLE_ALIAS => $metaData->getEntityTable()]
        );

        return $this->baseSelectModifier->execute($select, (int) $storeId);
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
}
