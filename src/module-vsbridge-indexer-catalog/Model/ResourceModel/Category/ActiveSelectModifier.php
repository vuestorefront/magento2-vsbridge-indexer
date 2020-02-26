<?php

declare(strict_types=1);

/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category;

use Divante\VsbridgeIndexerCatalog\Model\CategoryMetaData;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Eav\Model\Entity\Attribute as Attribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ActiveSelectModifier
 */
class ActiveSelectModifier implements BaseSelectModifierInterface
{
    /**
     * @var LoadAttributes
     */
    private $loadAttributes;

    /**
     * @var CategoryMetaData
     */
    private $categoryMetadata;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * ActiveSelectModifier constructor.
     *
     * @param CategoryMetaData $metadataPool
     * @param StoreManagerInterface $storeManager
     * @param LoadAttributes $loadAttributes
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        CategoryMetaData $metadataPool,
        StoreManagerInterface $storeManager,
        LoadAttributes $loadAttributes,
        ResourceConnection $resourceConnection
    ) {
        $this->storeManager = $storeManager;
        $this->categoryMetadata = $metadataPool;
        $this->loadAttributes = $loadAttributes;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Process the select statement - filter categories by vendor
     *
     * @param Select $select
     * @param int $storeId
     *
     * @return Select
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute(Select $select, int $storeId): Select
    {
        $linkField = $this->categoryMetadata->get()->getLinkField();

        $attribute = $this->getIsActiveAttribute();
        $checkSql = $this->getConnection()->getCheckSql('c.value_id > 0', 'c.value', 'd.value');
        $attributeId = (int) $attribute->getId();
        $backendTable = $this->resourceConnection->getTableName($attribute->getBackendTable());

        $joinCondition = [
            'd.attribute_id = ?',
            'd.store_id = 0',
            "d.$linkField = entity.$linkField",
        ];

        $defaultJoinCond = $this->getConnection()->quoteInto(
            implode(' AND ', $joinCondition),
            $attributeId
        );

        $storeJoinCond = [
            $this->getConnection()->quoteInto("c.attribute_id = ?", $attributeId),
            $this->getConnection()->quoteInto("c.store_id = ?", $storeId),
            "c.$linkField = entity.$linkField",
        ];

        $select->joinLeft(
            ['d' => $backendTable],
            $defaultJoinCond,
            []
        )->joinLeft(
            ['c' => $backendTable],
            implode(' AND ', $storeJoinCond),
            []
        )->where(sprintf("%s = 1", $checkSql));

        return $select;
    }

    /**
     * Retrieve Category Metadata
     *
     * @return EntityMetadataInterface
     */
    private function getCategoryMetadata(): EntityMetadataInterface
    {
        return $this->categoryMataData->get();
    }

    /**
     * Retrieve Vendor Attribute
     *
     * @return Attribute
     * @throws LocalizedException
     */
    private function getIsActiveAttribute(): Attribute
    {
        return $this->loadAttributes->getAttributeByCode('is_active');
    }

    /**
     * Retrieve Connection
     *
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        return $this->resourceConnection->getConnection();
    }
}
