<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product;

use Magento\Framework\App\ResourceConnection;
use Magento\Customer\Model\Group;
use Divante\VsbridgeIndexerCatalog\Model\ProductMetaData;

/**
 * Class TierPrices
 */
class TierPrices
{

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var ProductMetaData
     */
    private $productMetaData;

    /**
     * TierPrices constructor.
     *
     * @param ResourceConnection $resourceModel
     * @param ProductMetaData $productMetaData
     */
    public function __construct(
        ResourceConnection $resourceModel,
        ProductMetaData $productMetaData
    ) {
        $this->resource = $resourceModel;
        $this->productMetaData = $productMetaData;
    }

    /**
     * @param int $websiteId
     * @param array $linkFieldIds
     *
     * @return array
     * @throws \Exception
     */
    public function loadTierPrices($websiteId, array $linkFieldIds)
    {
        $linkField = $this->productMetaData->get()->getLinkField();

        $columns = [
            'price_id' => 'value_id',
            'website_id' => 'website_id',
            'all_groups' => 'all_groups',
            'cust_group' => 'customer_group_id',
            'price_qty' => 'qty',
            'price' => 'value',
            $linkField => $linkField,
        ];

        $select = $this->getConnection()->select()
            ->from($this->resource->getTableName('catalog_product_entity_tier_price'), $columns)
            ->where("$linkField IN(?)", $linkFieldIds)
            ->order(
                [
                    $linkField,
                    'qty',
                ]
            );

        if ($websiteId === 0) {
            $select->where('website_id = ?', $websiteId);
        } else {
            $select->where(
                'website_id IN (?)',
                [
                    '0',
                    $websiteId,
                ]
            );
        }

        $tierPrices = [];

        foreach ($this->getConnection()->fetchAll($select) as $row) {
            $tierPrices[$row[$linkField]][] = [
                'website_id' => (int)$row['website_id'],
                'cust_group' => $row['all_groups'] ? Group::CUST_GROUP_ALL
                    : (int)$row['cust_group'],
                'price_qty' => (float)$row['price_qty'],
                'price' => (float)$row['price'],
                'website_price' => (float)$row['price'],
            ];
        }

        return $tierPrices;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
}
