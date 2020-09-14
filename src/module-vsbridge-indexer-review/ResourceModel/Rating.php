<?php

declare(strict_types = 1);

/**
 * @package  Divante\VsbridgeIndexerReview
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerReview\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Class Rating
 */
class Rating
{

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var []
     */
    private $ratingTitlesByStore;

    /**
     * Rating constructor.
     *
     * @param Review $reviewResourceModel
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resource = $resourceConnection;
    }

    /**
     * @param array $reviewIds
     *
     * @return array
     */
    public function getRatings(array $reviewIds): array
    {
        $select = $this->getConnection()->select()->from(
            ['r' => $this->resource->getTableName('rating_option_vote')],
            [
                'review_id',
                'rating_id',
                'percent',
                'value',
            ]
        );

        $select->where('r.review_id IN (?)', $reviewIds);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @param int $ratingId
     * @param int $storeId
     *
     * @return string
     */
    public function getRatingTitleById(int $ratingId, int $storeId): string
    {
        $titles = $this->getRatingTitle($storeId);

        return $titles[$ratingId];
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    private function getRatingTitle(int $storeId): array
    {
        if (!isset($this->ratingTitlesByStore[$storeId])) {
            $connection = $this->getConnection();
            $table = $this->resource->getTableName('rating');
            $select = $connection->select()->from($table, ['rating_id']);
            $select->where('entity_id = ?', $this->getEntityId());
            $codeExpr = $connection->getIfNullSql('title.value', "{$table}.rating_code");
            $select->joinLeft(
                ['title' => $this->resource->getTableName('rating_title')],
                $connection->quoteInto("{$table}.rating_id = title.rating_id AND title.store_id = ?", $storeId),
                ['title' => $codeExpr]
            );

            $this->ratingTitlesByStore[$storeId] = $connection->fetchPairs($select);
        }

        return $this->ratingTitlesByStore[$storeId];
    }

    /**
     * @return int
     */
    public function getEntityId(): int
    {
        if (null === $this->entityId) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from($this->resource->getTableName('rating_entity'), ['entity_id'])
                ->where('entity_code = :entity_code');

            $entityId = $connection->fetchOne(
                $select,
                [':entity_code' => \Magento\Review\Model\Rating::ENTITY_PRODUCT_CODE]
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
