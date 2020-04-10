<?php
/**
 * Copyright Divante Sp. z o.o.
 * See LICENSE_DIVANTE.txt for license details.
 */
namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Type\Grouped;

use Magento\Framework\App\ResourceConnection;
use Divante\VsbridgeIndexerCatalog\Model\Product\GetParentsByChildIdInterface;
use Magento\Framework\DB\Select;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link;

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
     * GetParentsByChildId constructor.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
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
        $parentSku = [];
        $connection = $this->resourceConnection->getConnection();
        $select = $this->buildSelect($childId);
        $result = $connection->fetchAll($select);
        $productIds = array_column($result, 'product_id');

        $sku = $this->getProductSkusByIds($productIds);

        foreach ($result as $row) {
            $parentSku[$row['linked_product_id']] = $parentSku[$row['linked_product_id']] ?? [];
            $parentSku[$row['linked_product_id']][] = $sku[$row['product_id']];
        }

        return $parentSku;
    }

    /**
     * Build Select
     *
     * @param array $childId
     *
     * @return Select
     */
    private function buildSelect(array $childId): Select
    {
        $connection = $this->resourceConnection->getConnection();

        return $connection->select()->from(
            $this->resourceConnection->getTableName('catalog_product_link'),
            ['product_id', 'linked_product_id']
        )->where(
            'linked_product_id IN(?)',
            $childId
        )->where(
            'link_type_id = ?',
            Link::LINK_TYPE_GROUPED
        );
    }

    /**
     * Retrieve sku for parents
     *
     * @param array $productIds
     *
     * @return array
     */
    private function getProductSkusByIds(array $productIds): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from('catalog_product_entity', ['entity_id', 'sku'])
            ->where('entity_id IN (?)', $productIds);

        return $connection->fetchPairs($select);
    }
}
