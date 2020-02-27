<?php

declare(strict_types=1);

/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product;

use Divante\VsbridgeIndexerCatalog\Model\ProductMetaData;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class StatusSelectModifier
 */
class StatusSelectModifier implements BaseSelectModifierInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AttributeDataProvider
     */
    private $attributeDataProvider;

    /**
     * @var ProductMetaData
     */
    private $productMetaData;

    /**
     * Product constructor.
     *
     * @param AttributeDataProvider $attributeDataProvider
     * @param ResourceConnection $resourceConnection
     * @param ProductMetaData $productMetaData
     */
    public function __construct(
        AttributeDataProvider $attributeDataProvider,
        ResourceConnection $resourceConnection,
        ProductMetaData $productMetaData
    ) {
        $this->attributeDataProvider = $attributeDataProvider;
        $this->resourceConnection = $resourceConnection;
        $this->productMetaData = $productMetaData;
    }

    /**
     * @param Select $select
     * @param int $storeId
     *
     * @return Select
     *
     * @throws LocalizedException
     */
    public function execute(Select $select, int $storeId): Select
    {
        $attribute = $this->getStatusAttribute();
        $backendTable = $this->resourceConnection->getTableName($attribute->getBackendTable());
        $checkSql = $this->getConnection()->getCheckSql('c.value_id > 0', 'c.value', 'd.value');

        $defaultJoinCond = $this->getDefaultJoinConditions();
        $storeJoinCond = $this->getStoreJoinConditions($storeId);

        $select->joinLeft(
            ['d' => $backendTable],
            $defaultJoinCond,
            []
        )->joinLeft(
            ['c' => $backendTable],
            $storeJoinCond,
            []
        )->where($checkSql . ' = ?', Status::STATUS_ENABLED);

        return $select;
    }

    /**
     * Retrieve Store join conditions
     *
     * @param int $storeId
     *
     * @return string
     * @throws LocalizedException
     */
    private function getStoreJoinConditions(int $storeId): string
    {
        $linkFieldId = $this->productMetaData->get()->getLinkField();
        $attribute = $this->getStatusAttribute();
        $attributeId = (int) $attribute->getId();

        $storeJoinCond = [
            $this->getConnection()->quoteInto("c.attribute_id = ?", $attributeId),
            $this->getConnection()->quoteInto("c.store_id = ?", $storeId),
            sprintf('c.%s = %s.%s', $linkFieldId, Product::MAIN_TABLE_ALIAS, $linkFieldId),
        ];

        return implode(' AND ', $storeJoinCond);
    }

    /**
     * Get Default Join Conditions
     *
     * @return string
     *
     * @throws LocalizedException
     */
    private function getDefaultJoinConditions(): string
    {
        $linkFieldId = $this->productMetaData->get()->getLinkField();
        $attribute = $this->getStatusAttribute();
        $attributeId = (int) $attribute->getId();

        $joinCondition = [
            'd.attribute_id = ?',
            'd.store_id = 0',
            sprintf('d.%s = %s.%s', $linkFieldId, Product::MAIN_TABLE_ALIAS, $linkFieldId),
        ];

        return $this->getConnection()->quoteInto(
            implode(' AND ', $joinCondition),
            $attributeId
        );
    }

    /**
     * Get Connection
     *
     * @return AdapterInterface
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }

    /**
     * Get status attribute id
     *
     * @return Attribute
     *
     * @throws LocalizedException
     */
    private function getStatusAttribute(): Attribute
    {
        return $this->attributeDataProvider->getAttributeByCode('status');
    }
}
