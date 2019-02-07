<?php
/**
 * @package   magento-2-1.dev
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Divante\VsbridgeIndexerCatalog\Model\CategoryMetaData;
use Magento\Catalog\Model\Category as CoreCategoryModel;

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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CategoryMetaData
     */
    private $categoryMetaData;

    /**
     * Category constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param CategoryMetaData $categoryMetaData
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        CategoryMetaData $categoryMetaData,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resourceConnection;
        $this->categoryMetaData = $categoryMetaData;
        $this->storeManager = $storeManager;
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
        $metaData = $this->categoryMetaData->get();
        $rootCategoryId = $this->storeManager->getStore($storeId)->getRootCategoryId();

        $select = $this->getConnection()->select()->from(
            ['entity' => $metaData->getEntityTable()]
        );

        if (!empty($categoryIds)) {
            $select->where('entity.entity_id IN (?)', $categoryIds);
        }

        $path = "1/{$rootCategoryId}%";
        $select->where('path LIKE ?', $path);
        $select->where('entity.entity_id > ?', $fromId);
        $select->limit($limit);
        $select->order('entity.entity_id ASC');

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
            ['entity' => $metaData->getEntityTable()],
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
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
}
