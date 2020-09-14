<?php

declare(strict_types = 1);

/**
 * @package  Divante\VsbridgeIndexerReview
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerReview\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * Class Review
 */
class Review
{

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var int
     */
    private $entityId;

    /**
     * Rates constructor.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resource = $resourceConnection;
    }

    /**
     * @param int $storeId
     * @param array $reviewIds
     * @param int $fromId
     * @param int $limit
     *
     * @return array
     */
    public function getReviews(int $storeId = 1, array $reviewIds = [], int $fromId = 0, int $limit = 1000): array
    {
        $select = $this->getConnection()->select()
            ->from(
                ['main_table' => $this->resource->getTableName('review')],
                [
                    'review_id',
                    'created_at',
                    'entity_pk_value',
                    'status_id',
                ]
            );

        $select->joinLeft(
            ['store' => $this->resource->getTableName('review_store')],
            'main_table.review_id = store.review_id'
        )->where('store.store_id = ?', $storeId);

        if (!empty($reviewIds)) {
            $select->where('main_table.review_id IN (?)', $reviewIds);
        }

        $select->where('entity_id = ? ', $this->getEntityId());
        $select = $this->joinReviewDetails($select);

        $select->where('main_table.status_id = ?', 1);
        $select->where('main_table.review_id > ?', $fromId);
        $select->order('main_table.review_id');
        $select->limit($limit);

        return $this->getConnection()->fetchAssoc($select);
    }

    /**
     * @param Select $select
     *
     * @return Select
     */
    private function joinReviewDetails(Select $select): Select
    {
        $select->joinLeft(
            ['detail' => $this->resource->getTableName('review_detail')],
            'main_table.review_id = detail.review_id',
            [
                'title',
                'nickname',
                'customer_id',
                'detail',
            ]
        );

        return $select;
    }

    /**
     * @return int
     */
    public function getEntityId(): int
    {
        if (null === $this->entityId) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from($this->resource->getTableName('review_entity'), ['entity_id'])
                ->where('entity_code = :entity_code');

            $entityId = $connection->fetchOne(
                $select,
                [':entity_code' => \Magento\Review\Model\Review::ENTITY_PRODUCT_CODE]
            );

            $this->entityId = (int) $entityId;
        }

        return $this->entityId;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
}
