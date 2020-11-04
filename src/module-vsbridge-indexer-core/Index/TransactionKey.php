<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Index;

use Divante\VsbridgeIndexerCore\Api\Index\TransactionKeyInterface;

/**
 * Class TransactionKey
 */
class TransactionKey implements TransactionKeyInterface
{
    /**
     * @var int|string
     */
    private $key;

    /**
     * @inheritdoc
     */
    public function load()
    {
        if (null === $this->key) {
            $currentDate = new \DateTime();
            $this->key = $currentDate->getTimestamp();
        }

        return $this->key;
    }
}
