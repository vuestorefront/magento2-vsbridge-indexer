<?php

namespace Divante\VsbridgeIndexerCore\Console\Command\Rebuild;

use Magento\Indexer\Model\Indexer\CollectionFactory;

/**
 * Vsbridge Indices providers
 */
class IndicesProvider
{
    const VSBRIDGE_INDEXER_PREFIX = 'vsbridge_';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array
     */
    private $excludedIndices;

    /**
     * IndicesProvider constructor.
     * @param CollectionFactory $collectionFactory
     * @param array $excludedIndices
     */
    public function __construct(CollectionFactory $collectionFactory, array $excludedIndices = [])
    {
        $this->collectionFactory = $collectionFactory;
        $this->excludedIndices = $excludedIndices;
    }

    /**
     * @return \Magento\Framework\Indexer\IndexerInterface[]
     */
    public function getValidIndices()
    {
        $indexers = $this->collectionFactory->create()->getItems();

        return array_filter($indexers, function ($indexer) {
            if (in_array($indexer->getId(), $this->excludedIndices)) {
                return false;
            }

            return substr($indexer->getId(), 0, 9) === self::VSBRIDGE_INDEXER_PREFIX;
        });
    }

    /**
     * @return string[]
     */
    public function getInvalidIndicesNames(): array
    {
        $invalid = [];

        foreach ($this->getValidIndices() as $indexer) {
            if (!$indexer->isWorking()) {
                continue;
            }

            $invalid[] = $indexer->getTitle();
        }

        return $invalid;
    }
}
