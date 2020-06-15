<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel;

use Divante\VsbridgeIndexerCatalog\Api\CatalogConfigurationInterface;
use Divante\VsbridgeIndexerCatalog\Model\ProductMetaData;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\BaseSelectModifierInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Helper as DbHelper;
use Magento\Framework\DB\Select;

/**
 * Class Product
 */
class Product
{
    /**
     * Alias for catalog_product_entity table
     */
    const MAIN_TABLE_ALIAS = 'entity';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DbHelper
     */
    private $dbHelper;

    /**
     * @var CatalogConfigurationInterface
     */
    private $productSettings;

    /**
     * @var array
     */
    private $configurableAttributeIds;

    /**
     * @var ProductMetaData
     */
    private $productMetaData;

    /**
     * @var BaseSelectModifierInterface
     */
    private $baseSelectModifier;

    /**
     * Product constructor.
     *
     * @param CatalogConfigurationInterface $configSettings
     * @param BaseSelectModifierInterface $baseSelectModifier
     * @param ResourceConnection $resourceConnection
     * @param ProductMetaData $productMetaData
     * @param DbHelper $dbHelper
     */
    public function __construct(
        CatalogConfigurationInterface $configSettings,
        BaseSelectModifierInterface $baseSelectModifier,
        ResourceConnection $resourceConnection,
        ProductMetaData $productMetaData,
        DbHelper $dbHelper
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->dbHelper = $dbHelper;
        $this->productSettings = $configSettings;
        $this->baseSelectModifier = $baseSelectModifier;
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
        $tableName = self::MAIN_TABLE_ALIAS;

        if (!empty($productIds)) {
            $select->where(sprintf("%s.entity_id IN (?)", $tableName), $productIds);
        }

        $select->limit($limit);
        $select->where(sprintf("%s.entity_id > ?", $tableName), $fromId);
        $select->order(sprintf("%s.entity_id ASC", $tableName));

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
                [self::MAIN_TABLE_ALIAS => $this->productMetaData->get()->getEntityTable()],
                $requiredColumns
            );

        $select = $this->baseSelectModifier->execute($select, $storeId);

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
            'created_at',
            'updated_at',
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
            sprintf('link_table.product_id = %s.entity_id', self::MAIN_TABLE_ALIAS),
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
     */
    private function addProductTypeFilter(Select $select, $storeId)
    {
        $types = $this->productSettings->getAllowedProductTypes($storeId);

        if (!empty($types)) {
            $select->where(sprintf('%s.type_id IN (?)', self::MAIN_TABLE_ALIAS), $types);
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
     * Get list of attribute ids used to create configurable products
     * @return array
     */
    public function getConfigurableAttributeIds()
    {
        if (null === $this->configurableAttributeIds) {
            $select = $this->getConnection()->select();
            $select->from(
                $this->resourceConnection->getTableName('catalog_product_super_attribute'),
                ['attribute_id']
            );

            $select->distinct();

            $this->configurableAttributeIds = $this->getConnection()->fetchCol($select);
        }

        return $this->configurableAttributeIds;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }
}
