<?php
/**
 * Copyright Divante Sp. z o.o.
 * See LICENSE_DIVANTE.txt for license details.
 */
declare(strict_types=1);

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Type\Bundle;

use Divante\VsbridgeIndexerCatalog\Model\Product\GetParentsByChildIdInterface;
use Divante\VsbridgeIndexerCatalog\Model\ProductMetaData;
use Magento\Framework\App\ResourceConnection;

/**
 * Class GetParentsByChildId
 */
class GetParentsByChildId implements GetParentsByChildIdInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ProductMetaData
     */
    private $metadata;

    /**
     * GetParentsByChildId constructor.
     *
     * @param ProductMetaData $productMetaData
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ProductMetaData $productMetaData,
        ResourceConnection $resourceConnection
    ) {
        $this->metadata = $productMetaData;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritDoc
     *
     * @param array $childId
     *
     * @return array
     */
    public function execute(array $childId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->distinct(
            true
        )->from(
            $this->getMainTable(),
            'product_id'
        )->join(
            ['e' => $this->metadata->get()->getEntityTable()],
            'e.' . $this->metadata->get()->getLinkField() . ' = ' .  $this->getMainTable() . '.parent_product_id',
            ['e.sku as parent_sku']
        )->where(
            $this->getMainTable() . '.product_id IN(?)',
            $childId
        );

        $parentIds = [];

        foreach ($connection->fetchAll($select) as $row) {
            $parentIds[$row['product_id']] = $parentIds[$row['product_id']] ?? [];
            $parentIds[$row['product_id']][] = $row['parent_sku'];
        }

        return $parentIds;
    }

    /**
     * Retrieve Bundle Selection Table
     *
     * @return string
     */
    private function getMainTable(): string
    {
        return $this->resourceConnection->getTableName('catalog_product_bundle_selection');
    }
}
