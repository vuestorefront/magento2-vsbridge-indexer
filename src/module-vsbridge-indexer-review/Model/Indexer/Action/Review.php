<?php

declare(strict_types = 1);

/**
 * @package  Divante\VsbridgeIndexerReview
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerReview\Model\Indexer\Action;

use Divante\VsbridgeIndexerReview\ResourceModel\Review as ResourceModel;

/**
 * Class Review
 */
class Review
{
    /**
     * @var ResourceModel
     */
    private $resourceModel;

    /**
     * Review constructor.
     *
     * @param ResourceModel $resource
     */
    public function __construct(ResourceModel $resource)
    {
        $this->resourceModel = $resource;
    }

    /**
     * @param int $storeId
     * @param array $reviewIds
     *
     * @return \Traversable
     */
    public function rebuild(int $storeId = 1, array $reviewIds = [])
    {
        $lastReviewId = 0;

        do {
            $reviews = $this->resourceModel->getReviews($storeId, $reviewIds, $lastReviewId);

            foreach ($reviews as $review) {
                $review['id'] = (int)($review['review_id']);
                $review['product_id'] = (int)$review['entity_pk_value'];
                $review['review_status'] = $review['status_id'];
                $review['ratings'] = [];
                unset($review['review_id'], $review['entity_pk_value'], $review['status_id'], $review['store_id']);
                $lastReviewId = $review['id'];

                if (null !== $review['customer_id']) {
                    $review['customer_id'] = (int)$review['customer_id'];
                }

                yield $lastReviewId => $review;
            }
        } while (!empty($reviews));
    }
}
