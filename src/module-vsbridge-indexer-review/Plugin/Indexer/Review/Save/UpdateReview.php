<?php

/**
 * @package  Divante\VsbridgeIndexerReview
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerReview\Plugin\Indexer\Review\Save;

use Divante\VsbridgeIndexerReview\Model\Indexer\ReviewProcessor;
use Magento\Review\Model\Review;

/**
 * Class UpdateReview
 */
class UpdateReview
{

    /**
     * @var ReviewProcessor
     */
    private $reviewProcessor;

    /**
     * Save constructor.
     *
     * @param ReviewProcessor $reviewProcessor
     */
    public function __construct(ReviewProcessor $reviewProcessor)
    {
        $this->reviewProcessor = $reviewProcessor;
    }

    /**
     * @param Review $subject
     * @param Review $result
     *
     * @return Review
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAfterSave(Review $subject, Review $result)
    {
        $result->getResource()->addCommitCallback(function () use ($result) {
            $this->reviewProcessor->reindexRow($result->getId());
        });

        return $result;
    }

    /**
     * @param Review $subject
     * @param Review $result
     *
     * @return Review
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAfterDeleteCommit(Review $subject, Review $result)
    {
        $this->reviewProcessor->reindexRow($result->getId());

        return $result;
    }
}
