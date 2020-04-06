<?php
/**
 * @package  Divante\VsbridgeIndexerCore
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Index;

use Divante\VsbridgeIndexerCore\Api\BulkLoggerInterface;
use Divante\VsbridgeIndexerCore\Api\BulkResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BulkLogger
 */
class BulkLogger implements BulkLoggerInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * BulkLogger constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param BulkResponseInterface $bulkResponse
     *
     * @return void
     */
    public function log(BulkResponseInterface $bulkResponse)
    {
        if ($bulkResponse->hasErrors()) {
            $aggregateErrorsByReason = $bulkResponse->aggregateErrorsByReason();

            foreach ($aggregateErrorsByReason as $error) {
                $docIds = implode(', ', array_slice($error['document_ids'], 0, 10));
                $errorMessages = [
                    sprintf(
                        'Bulk %s operation failed %d times in index %s for type %s.',
                        $error['operation'],
                        $error['count'],
                        $error['index'],
                        $error['document_type']
                    ),
                    sprintf(
                        'Error (%s) : %s.',
                        $error['error']['type'],
                        $error['error']['reason']
                    ),
                    sprintf(
                        'Failed doc ids sample : %s.',
                        $docIds
                    ),
                ];

                $this->logger->error(implode(' ', $errorMessages));
            }
        }
    }
}
