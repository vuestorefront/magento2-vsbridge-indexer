<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel;

use Divante\VsbridgeIndexerCatalog\Api\Data\CatalogConfigurationInterface;
use Divante\VsbridgeIndexerCatalog\Model\ProductMetaData;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\AttributeDataProvider;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Helper as DbHelper;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Product
 */
class Product
{

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DbHelper
     */
    private $dbHelper;

    /**
     * @var AttributeDataProvider
     */
    private $attributeDataProvider;

    /**
     * @var CatalogConfigurationInterface
     */
    private $productSettings;

    /**
     * @var int
     */
    private $statusAttributeId;

    /**
     * @var ProductMetaData
     */
    private $productMetaData;

    /**
     * Product constructor.
     *
     * @param CatalogConfigurationInterface $configSettings
     * @param AttributeDataProvider $attributeDataProvider
     * @param ResourceConnection $resourceConnection
     * @param StoreManagerInterface $storeManager
     * @param ProductMetaData $productMetaData
     * @param DbHelper $dbHelper
     */
    public function __construct(
        CatalogConfigurationInterface $configSettings,
        AttributeDataProvider $attributeDataProvider,
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager,
        ProductMetaData $productMetaData,
        DbHelper $dbHelper
    ) {
        $this->attributeDataProvider = $attributeDataProvider;
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->dbHelper = $dbHelper;
        $this->productSettings = $configSettings;
        $this->productMetaData = $productMetaData;
    }

    /**
     * @param int $storeId
     * @param array $productIds
     * @param int $fromId
     * @param int $limit
     *
     * @return array
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProducts($storeId = 1, array $productIds = [], $fromId = 0, $limit = 1000)
    {
        $select = $this->prepareBaseProductSelect($this->getRequiredColumns(), $storeId);
        $select = $this->addProductTypeFilter($select, $storeId);

        if (!empty($productIds)) {
            $select->where('entity.entity_id IN (?)', $productIds);
        }

        $select->limit($limit);
        $select->where('entity.entity_id > ?', $fromId);
        $select->order('entity.entity_id ASC');

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @param array $requiredColumns
     * @param int $storeId
     *
     * @return Select
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareBaseProductSelect(array $requiredColumns, int $storeId)
    {
        $select = $this->getConnection()->select()
            ->from(
                ['entity' => $this->productMetaData->get()->getEntityTable()],
                $requiredColumns
            );

        $select = $this->addStatusFilter($select, $storeId);
        $select = $this->addWebsiteFilter($select, $storeId);

        return $select;
    }

    /**
     * @return array
     */
    private function getRequiredColumns()
    {
        $productMetaData = $this->productMetaData->get();
        $columns = [
            'entity_id',
            'attribute_set_id',
            'type_id',
            'sku',
        ];

        $linkField = $productMetaData->getLinkField();

        if ($productMetaData->getIdentifierField() !== $linkField) {
            $columns[] = $linkField;
        }

        return $columns;
    }

    /**
     * @param array $parentIds
     * @param int $storeId
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadChildrenProducts(array $parentIds, $storeId)
    {
        $linkField = $this->productMetaData->get()->getLinkField();
        $entityId = $this->productMetaData->get()->getIdentifierField();
        $columns = [
            'sku',
            'type_id',
            $entityId,
        ];

        if ($linkField !== $entityId) {
            $columns[] = $linkField;
        }

        $select = $this->prepareBaseProductSelect($columns, $storeId);

        $select->join(
            ['link_table' => $this->resourceConnection->getTableName('catalog_product_super_link')],
            'link_table.product_id = entity.entity_id',
            []
        );

        $select->where('link_table.parent_id IN (?)', $parentIds);
        $select->group('entity_id');

        $this->dbHelper->addGroupConcatColumn($select, 'parent_ids', 'parent_id');

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @param int $storeId
     *
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function addWebsiteFilter(Select $select, $storeId)
    {
        $websiteId = $this->getWebsiteId($storeId);
        $indexTable = $this->resourceConnection->getTableName('catalog_product_website');

        $conditions = ['websites.product_id = entity.entity_id'];
        $conditions[] = $this->getConnection()->quoteInto('websites.website_id = ?', $websiteId);

        $select->join(['websites' => $indexTable], join(' AND ', $conditions), []);

        return $select;
    }

    /**
     * @param int $storeId
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getWebsiteId($storeId)
    {
        $store = $this->storeManager->getStore($storeId);

        return $store->getWebsiteId();
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @param int $storeId
     *
     * @return \Magento\Framework\DB\Select
     */
    private function addProductTypeFilter(Select $select, $storeId)
    {
        $types = $this->productSettings->getAllowedProductTypes($storeId);

        if (!empty($types)) {
            $select->where('entity.type_id IN (?)', $types);
        }

        return $select;
    }

    /**
     * @param array $childrenIds
     *
     * @return array
     * @throws \Exception
     */
    public function getRelationsByChild(array $childrenIds)
    {
        $metadata = $this->productMetaData->get();
        $linkFieldId = $metadata->getLinkField();
        $entityTable = $this->resourceConnection->getTableName($metadata->getEntityTable());
        $relationTable = $this->resourceConnection->getTableName(('catalog_product_relation'));
        $joinCondition = "relation.parent_id = entity.$linkFieldId";

        $select = $this->getConnection()->select()
            ->from(['relation' => $relationTable], [])
            ->join(['entity' => $entityTable], $joinCondition, [$metadata->getIdentifierField()])
            ->where('child_id IN(?)', array_map('intval', $childrenIds));

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * @param array $productIds
     * @return array
     */
    public function getSkusByIds(array $productIds)
    {
        $select = $this->getConnection()->select()
            ->from(['e' => $this->resourceConnection->getTableName('catalog_product_entity')]);
        $select->where('e.entity_id IN (?)', $productIds);
        $select->reset(Select::COLUMNS);
        $select->columns(['sku']);

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @param int $storeId
     *
     * @return \Magento\Framework\DB\Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addStatusFilter(Select $select, $storeId)
    {
        $linkFieldId = $this->productMetaData->get()->getLinkField();

        $backendTable = $this->resourceConnection->getTableName(
            [
                'catalog_product_entity',
                'int',
            ]
        );
        $checkSql = $this->getConnection()->getCheckSql('c.value_id > 0', 'c.value', 'd.value');
        $attributeId = (int) $this->getStatusAttributeId();

        $joinCondition = [
            'd.attribute_id = ?',
            'd.store_id = 0',
            "d.$linkFieldId = entity.$linkFieldId",
        ];

        $defaultJoinCond = $this->getConnection()->quoteInto(
            implode(' AND ', $joinCondition),
            $attributeId
        );

        $storeJoinCond = [
            $this->getConnection()->quoteInto("c.attribute_id = ?", $attributeId),
            $this->getConnection()->quoteInto("c.store_id = ?", $storeId),
            "c.$linkFieldId = entity.$linkFieldId",
        ];

        $select->joinLeft(
            ['d' => $backendTable],
            $defaultJoinCond,
            []
        )->joinLeft(
            ['c' => $backendTable],
            implode(' AND ', $storeJoinCond),
            []
        )->where($checkSql . ' = ?', Status::STATUS_ENABLED);

        return $select;
    }

    /**
     * Get status attribute id
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getStatusAttributeId()
    {
        if ($this->statusAttributeId === null) {
            $this->statusAttributeId = (int) $this->attributeDataProvider
                ->getAttributeByCode('status')
                ->getAttributeId();
        }

        return $this->statusAttributeId;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }
}
