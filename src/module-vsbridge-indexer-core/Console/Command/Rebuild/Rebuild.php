<?php

namespace Divante\VsbridgeIndexerCore\Console\Command\Rebuild;

use Divante\VsbridgeIndexerCore\Api\IndexOperationInterface;
use Divante\VsbridgeIndexerCore\Index\IndexSettings;
use Divante\VsbridgeIndexerCore\Indexer\StoreManager;
use Divante\VsbridgeIndexerCore\Model\IndexerRegistry;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Api\Data\StoreInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Divante\VsbridgeIndexerCore\Model\ElasticsearchResolverInterface;

/**
 * Class responsible for running vsbridge indexers - exporting data to ES
 */
class Rebuild
{
    /**
     * @var IndexOperationInterface
     */
    private $indexOperations;

    /**
     * @var StoreManager
     */
    private $indexerStoreManager;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ElasticsearchResolverInterface
     */
    private $elasticsearchResolver;

    /**
     * @var IndicesProvider
     */
    private $indicesProvider;

    /**
     * Rebuild constructor.
     * @param IndexOperationInterface $indexOperations
     * @param StoreManager $indexerStoreManager
     * @param IndexerRegistry $indexerRegistry
     * @param ElasticsearchResolverInterface $elasticsearchResolver
     * @param OutputInterface $output
     * @param IndicesProvider $indicesProvider
     */
    public function __construct(
        IndexOperationInterface $indexOperations,
        StoreManager $indexerStoreManager,
        IndexerRegistry $indexerRegistry,
        ElasticsearchResolverInterface $elasticsearchResolver,
        OutputInterface $output,
        IndicesProvider $indicesProvider
    ) {
        $this->indexOperations = $indexOperations;
        $this->indexerStoreManager = $indexerStoreManager;
        $this->indexerRegistry = $indexerRegistry;
        $this->output = $output;
        $this->indicesProvider = $indicesProvider;
        $this->elasticsearchResolver = $elasticsearchResolver;
    }

    /**
     * @param string|null $storeId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(string $storeId = null)
    {
        if (!$this->validate()) {
            return;
        }

        if ($storeId && !$this->indexerStoreManager->isStoreAllowedToReindex($storeId)) {
            $this->output->writeln("Store " . $storeId . " is not allowed.");
            return;
        }

        $storeList = $this->indexerStoreManager->getStores($storeId);

        foreach ($storeList as $store) {
            $this->output->writeln(
                "Reindexing all VS indexes for store " . $store->getName() . "..."
            );
            $this->reindexStore($store);
            $this->output->writeln("Reindexing has completed!");
        }
    }

    /**
     * Validate indices
     *
     * @return bool
     */
    private function validate(): bool
    {
        $invalidIndices = $this->indicesProvider->getInvalidIndicesNames();

        if (!empty($invalidIndices)) {
            $message = 'Some indices has invalid status: '. implode(', ', $invalidIndices) . '. ';
            $message .= 'Please change indices status to VALID manually or use bin/magento vsbridge:reset command.';
            $this->output->writeln("<info>WARNING: Indexation can't be executed. $message</info>");

            return false;
        }

        return true;
    }

    /**
     * Reindex each vsbridge index for the specified store
     *
     * @param StoreInterface $store
     */
    private function reindexStore(StoreInterface $store)
    {
        $this->indexerStoreManager->override([$store]);
        $esVersion = $this->elasticsearchResolver->getVersion();

        if ($esVersion === ElasticsearchResolverInterface::DEFAULT_ES_VERSION) {
            $index = $this->indexOperations->createIndex(IndexSettings::DUMMY_INDEX_IDENTIFIER, $store);
            $this->indexerRegistry->setFullReIndexationIsInProgress();
        }

        foreach ($this->indicesProvider->getValidIndices() as $indexer) {
            try {
                $startTime = microtime(true);
                $indexer->reindexAll();

                $resultTime = microtime(true) - $startTime;

                $this->output->writeln(
                    $indexer->getTitle() . ' index has been rebuilt successfully in ' . gmdate('H:i:s', $resultTime)
                );
            } catch (LocalizedException $e) {
                $this->output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            } catch (\Exception $e) {
                $this->output->writeln($indexer->getTitle());
                $this->output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            }
        }

        if ($esVersion === ElasticsearchResolverInterface::DEFAULT_ES_VERSION) {
            $this->indexOperations->switchIndexer($store->getId(), $index->getName(), $index->getAlias());
            $this->output->writeln(
                sprintf('<info>Index name: %s, index alias: %s</info>', $index->getName(), $index->getAlias())
            );
        }
    }
}
