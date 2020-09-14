<?php
/**
 * @package  Divante\VsbridgeIndexerReview
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types = 1);

namespace Divante\VsbridgeIndexerReview\Model\Indexer\DataProvider;

use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeIndexerReview\Model\ResourceModel\Rating as RatingResourceModel;

/**
 * Class Ratings
 */
class Ratings implements DataProviderInterface
{
    /**
     * @var RatingResourceModel
     */
    private $ratingResourceModel;

    /**
     * Ratings constructor.
     *
     * @param RatingResourceModel $ratingResourceModel
     */
    public function __construct(RatingResourceModel $ratingResourceModel)
    {
        $this->ratingResourceModel = $ratingResourceModel;
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $reviewIds = array_keys($indexData);
        $ratings = $this->ratingResourceModel->getRatings($reviewIds);

        foreach ($ratings as $rating) {
            $reviewId = $rating['review_id'];
            $ratingId = (int)$rating['rating_id'];
            $title = $this->ratingResourceModel->getRatingTitleById($ratingId, $storeId);
            $indexData[$reviewId]['ratings'][] = [
                'title' => $title,
                'percent' => (int)$rating['percent'],
                'value' => (int)$rating['value'],
            ];
        }

        return $indexData;
    }
}
