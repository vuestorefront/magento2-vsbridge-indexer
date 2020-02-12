<?php declare(strict_types=1);
/**
 * @package  Divante\VsbridgeIndexerCore
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Client;

use Divante\VsbridgeIndexerCore\Api\Client\ClientInterface;
use Divante\VsbridgeIndexerCore\Exception\ConnectionDisabledException;

/**
 * Interface ClientProviderInterface
 */
interface ClientProviderInterface
{
    /**
     * Retrieve client for store
     *
     * @param int $storeId
     *
     * @return ClientInterface
     * @throws ConnectionDisabledException
     */
    public function getClient(int $storeId): ClientInterface;
}
