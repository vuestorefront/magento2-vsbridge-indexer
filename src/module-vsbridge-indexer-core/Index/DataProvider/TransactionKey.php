<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Index\DataProvider;

use Divante\VsbridgeIndexerCore\Api\Index\TransactionKeyInterface;
use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;

/**
 * Class TransactionKey
 */
class TransactionKey implements DataProviderInterface
{
    private $transactionKey;

    /**
     * TransactionKey constructor.
     *
     * @param TransactionKeyInterface $transactionKey
     */
    public function __construct(TransactionKeyInterface $transactionKey)
    {
        $this->transactionKey = $transactionKey->load();
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        foreach ($indexData as &$data) {
            $data['tsk'] = $this->transactionKey;
        }

        return $indexData;
    }
}
