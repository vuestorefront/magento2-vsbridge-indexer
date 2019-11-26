<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\App\ResourceConnection;
use Divante\VsbridgeIndexerCatalog\Model\CategoryMetaData;

/**
 * Class Children
 */
class Children
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var int
     */
    private $isActiveAttributeId;

    /**
     * @var AttributeDataProvider
     */
    private $attributeDataProvider;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CategoryMetaData
     */
    private $categoryMetaData;

    /**
     * Children constructor.
     *
     * @param AttributeDataProvider $attributeDataProvider
     * @param CollectionFactory $collectionFactory
     * @param ResourceConnection $resourceModel
     * @param CategoryMetaData $categoryMetaData
     */
    public function __construct(
        AttributeDataProvider $attributeDataProvider,
        CollectionFactory $collectionFactory,
        ResourceConnection $resourceModel,
        CategoryMetaData $categoryMetaData
    ) {
        $this->attributeDataProvider = $attributeDataProvider;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resourceModel;
        $this->categoryMetaData = $categoryMetaData;
    }

    /**
     * @param array $category
     * @param int $storeId
     *
     * @return array
     * @throws \Exception
     */
    public function loadChildren(array $category, $storeId)
    {
        $childIds = $this->getChildrenIds($category, $storeId);

        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addIsActiveFilter();

        $select = $collection->getSelect();
        $select->where('e.entity_id IN (?)', $childIds);
        $select->order('path asc');
        $select->order('position asc');

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @param array $category
     * @param int $storeId
     * @param bool $recursive
     *
     * @return array
     * @throws \Exception
     */
    private function getChildrenIds(array $category, $storeId, $recursive = true)
    {
        $linkField = $this->categoryMetaData->get()->getLinkField();
        $attributeId = $this->getIsActiveAttributeId();
        $backendTable = $this->resource->getTableName([$this->getEntityTable(), 'int']);
        $connection = $this->getConnection();
        $checkSql = $connection->getCheckSql('c.value_id > 0', 'c.value', 'd.value');
        $bind = [
            'attribute_id' => $attributeId,
            'store_id' => $storeId,
            'scope' => 1,
            'c_path' => $category['path'] . '/%',
        ];
        $select = $this->getConnection()->select()->from(
            ['m' => $this->getEntityTable()],
            'entity_id'
        )->joinLeft(
            ['d' => $backendTable],
            "d.attribute_id = :attribute_id AND d.store_id = 0 AND d.{$linkField} = m.{$linkField}",
            []
        )->joinLeft(
            ['c' => $backendTable],
            "c.attribute_id = :attribute_id AND c.store_id = :store_id AND c.{$linkField} = m.{$linkField}",
            []
        )->where(
            $checkSql . ' = :scope'
        )->where(
            $connection->quoteIdentifier('path') . ' LIKE :c_path'
        );

        if (!$recursive) {
            $select->where($connection->quoteIdentifier('level') . ' <= :c_level');
            $bind['c_level'] = $category['level'] + 1;
        }

        return $this->getConnection()->fetchCol($select, $bind);
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getEntityTable()
    {
        return $this->categoryMetaData->get()->getEntityTable();
    }

    /**
     * Get "is_active" attribute identifier
     *
     * @return int
     */
    private function getIsActiveAttributeId()
    {
        if ($this->isActiveAttributeId === null) {
            $this->isActiveAttributeId = (int)$this->attributeDataProvider
                ->getAttributeByCode('is_active')
                ->getAttributeId();
        }

        return $this->isActiveAttributeId;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
}
