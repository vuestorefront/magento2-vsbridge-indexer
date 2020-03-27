<?php declare(strict_types=1);
/**
 * @package  Divante\VsbridgeIndexerCore
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Index;

use Divante\VsbridgeIndexerCore\Api\IndexOperationInterface;
use Divante\VsbridgeIndexerCore\Api\IndexOperationInterfaceFactory;
use Divante\VsbridgeIndexerCore\Api\Index\IndexOperationProviderInterface;
use Divante\VsbridgeIndexerCore\Client\ClientProviderInterface;

/**
 * Class IndexOperationProvider
 */
class IndexOperationProvider implements IndexOperationProviderInterface
{
    /**
     * @var IndexOperationInterfaceFactory
     */
    private $indexOperationFactory;

    /**
     * @var ClientProviderInterface
     */
    private $clientProvider;

    /**
     * @var IndexOperationInterface[]
     */
    private $operationByStore;

    /**
     * IndexOperationProvider constructor.
     *
     * @param ClientProviderInterface $clientProvider
     * @param IndexOperationInterfaceFactory $indexOperationFactory
     */
    public function __construct(
        ClientProviderInterface $clientProvider,
        IndexOperationInterfaceFactory $indexOperationFactory
    ) {
        $this->clientProvider = $clientProvider;
        $this->indexOperationFactory = $indexOperationFactory;
    }

    /**
     * @inheritdoc
     */
    public function getOperationByStore(int $storeId): IndexOperationInterface
    {
        if (!isset($this->operationByStore[$storeId])) {
            $client = $this->clientProvider->getClient($storeId);
            $operation = $this->indexOperationFactory->create(['client' => $client]);
            $this->operationByStore[$storeId] = $operation;
        }

        return $this->operationByStore[$storeId];
    }
}
