<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Api\Index;

/**
 * Interface TransactionKeyInterface
 */
interface TransactionKeyInterface
{
    /**
     * @return int|string
     */
    public function load();
}
