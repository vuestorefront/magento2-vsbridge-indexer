<?php declare(strict_types=1);
/**
 * @package  Divante\VsbridgeIndexerCore
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Api;

/**
 * Interface BulkLoggerInterface
 */
interface BulkLoggerInterface
{
    /**
     * @param BulkResponseInterface $bulkResponse
     *
     * @return void
     */
    public function log(BulkResponseInterface $bulkResponse);
}
