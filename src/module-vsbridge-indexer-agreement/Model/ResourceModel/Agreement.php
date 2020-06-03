<?php
/**
 * Copyright Divante Sp. z o.o.
 * See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerAgreement\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Store;

class Agreement
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var MetadataPool
     */
    private $metaDataPool;

    /**
     * Agreement constructor.
     *
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection
    ) {
        $this->resource = $resourceConnection;
        $this->metaDataPool = $metadataPool;
    }

    /**
     * @param int $storeId
     * @param array $agreementIds
     * @param int $fromId
     * @param int $limit
     *
     * @return array
     */
    public function loadAgreements($storeId = 1, array $agreementIds = [], $fromId = 0, $limit = 1000)
    {
        $select = $this->getConnection()->select()->from(['agreement' => 'checkout_agreement']);
        $select->join(
            ['store_table' => $this->resource->getTableName('checkout_agreement_store')],
            "agreement.agreement_id = store_table.agreement_id",
            []
        )->group("agreement.agreement_id");

        $select->where(
            'store_table.store_id IN (?)',
            [
                Store::DEFAULT_STORE_ID,
                $storeId,
            ]
        );

        if (!empty($agreementIds)) {
            $select->where('agreement.agreement_id IN (?)', $agreementIds);
        }

        $select->where('is_active = ?', 1);
        $select->where('agreement.agreement_id > ?', $fromId)
            ->limit($limit)
            ->order('agreement.agreement_id');

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
}
