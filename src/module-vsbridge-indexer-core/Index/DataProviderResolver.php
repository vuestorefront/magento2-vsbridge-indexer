<?php

declare(strict_types=1);

namespace Divante\VsbridgeIndexerCore\Index;

use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeIndexerCore\Api\Index\DataProviderResolverInterface;
use Divante\VsbridgeIndexerCore\Index\DataProvider\TransactionKey;

/**
 * @inheritdoc
 */
class DataProviderResolver implements DataProviderResolverInterface
{
    /**
     * @var TransactionKey
     */
    private $transactionKeyDataProvider;

    /**
     * @var DataProviderInterface[]
     */
    private $dataProviders;

    /**
     * DataProviderResolver constructor.
     * @param TransactionKey $transactionKey
     * @param array $dataProviders
     */
    public function __construct(
        TransactionKey $transactionKey,
        array $dataProviders = []
    ) {
        foreach ($dataProviders as $typeDataProviders) {
            $this->validateProviders($typeDataProviders);
        }

        $this->dataProviders = $dataProviders;
        $this->transactionKeyDataProvider = $transactionKey;
    }

    /**
     * Check if validators implements DataProviderInterface
     *
     * @param array $dataProviders
     *
     * @return void
     */
    private function validateProviders(array $dataProviders)
    {
        foreach ($dataProviders as $dataProvider) {
            if (! $dataProvider instanceof DataProviderInterface) {
                throw new \InvalidArgumentException(
                    'DataProvider must implement ' . DataProviderInterface::class
                );
            }
        }
    }

    /**
     * @param string $indexName
     * @return DataProviderInterface[]
     */
    public function getDataProviders(string $indexName)
    {
        $dataProviders = $this->dataProviders[$indexName] ?? [];
        $dataProviders[] = $this->transactionKeyDataProvider;

        return $dataProviders;
    }
}
