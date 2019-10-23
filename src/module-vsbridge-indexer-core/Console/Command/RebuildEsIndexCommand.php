<?php
/**
 * @package  Divante\VsbridgeIndexerCore
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Console\Command;

use Divante\VsbridgeIndexerCore\Indexer\StoreManager;
use Divante\VsbridgeIndexerCore\Api\IndexOperationInterface;
use Divante\VsbridgeIndexerCore\Model\IndexerRegistry as IndexerRegistry;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Indexer\Console\Command\AbstractIndexerCommand;
use Magento\Store\Api\Data\StoreInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManagerInterface;

/**
 * Class IndexerReindexCommand
 */
class RebuildEsIndexCommand extends AbstractIndexerCommand
{
    const INPUT_STORE = 'store';
    const INPUT_ALL_STORES = 'all';
    const INPUT_KEY_INDEXERS = 'index';

    const INDEX_IDENTIFIER = 'vue_storefront_catalog';

    /**
     * @var IndexOperationInterface
     */
    private $indexOperations;

    /**
     * @var StoreManager
     */
    private $indexerStoreManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var array
     */
    private $excludeIndices = [];

    /**
     * RebuildEsIndexCommand constructor.
     *
     * @param ObjectManagerFactory $objectManagerFactory
     * @param array $excludeIndices
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        array $excludeIndices = []
    ) {
        $this->excludeIndices = $excludeIndices;
        parent::__construct($objectManagerFactory);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('vsbridge:reindex')
            ->setDescription('Rebuild indexer in ES.')
            ->setDefinition($this->getInputList());

        $this->addOption(
            self::INPUT_STORE,
            null,
            InputOption::VALUE_REQUIRED,
            'Store ID or Store Code'
        );

        $this->addOption(
            self::INPUT_ALL_STORES,
            null,
            InputOption::VALUE_NONE,
            'Reindex all stores'
        );

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initObjectManager();
        $output->setDecorated(true);
        $storeId = $input->getOption(self::INPUT_STORE);
        $allStores = $input->getOption(self::INPUT_ALL_STORES);

        if ($storeId) {
            $store = $this->getStoreManager()->getStore($storeId);
            $output->writeln("<info>Reindexing VS indexes for store " . $store->getName() . "...</info>");

            $returnValue = $this->reindexStore($store, $input, $output);

            $output->writeln("<info>Reindexing has completed!</info>");

            return $returnValue;

        } elseif ($allStores) {
            $output->writeln("<info>Reindexing all stores...</info>");
            $returnValues = [];

            /** @var \Magento\Store\Api\Data\StoreInterface $store */
            foreach ($this->getStoreManager()->getStores() as $store) {
                $output->writeln("<info>Reindexing store " . $store->getName() . "...</info>");
                $returnValues[] = $this->reindexStore($store, $input, $output);
            }

            $output->writeln("<info>All stores have been reindexed!</info>");
            // If failure returned in any store return failure now
            return in_array(Cli::RETURN_FAILURE, $returnValues) ? Cli::RETURN_FAILURE : Cli::RETURN_SUCCESS;
        } else {
            $output->writeln(
                "<comment>Not enough information provided, nothing has been reindexed. Try using --help for more information.</comment>"
            );
        }
    }

    /**
     * Reindex each vsbridge index for the specified store
     *
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    private function reindexStore(StoreInterface $store, InputInterface $input, OutputInterface $output)
    {
        $this->getIndexerStoreManager()->setLoadedStores([$store]);
        $index = $this->getIndexOperations()->createIndex(self::INDEX_IDENTIFIER, $store);
        $this->getIndexerRegistry()->setFullReIndexationIsInProgress();

        $returnValue = Cli::RETURN_FAILURE;

        foreach ($this->getIndexers($input) as $indexer) {
            if ($indexer->isWorking()) {
                $output->writeln($indexer->getTitle() . ' has been skipped. Change indexer status to valid.');
                continue;
            }

            try {
                $startTime = microtime(true);
                $indexer->reindexAll();

                $resultTime = microtime(true) - $startTime;
                $output->writeln(
                    $indexer->getTitle() . ' index has been rebuilt successfully in ' . gmdate('H:i:s', $resultTime)
                );
                $returnValue = Cli::RETURN_SUCCESS;
            } catch (LocalizedException $e) {
                $output->writeln("<error>" . $e->getMessage() . "</error>");
            } catch (\Exception $e) {
                $output->writeln("<error>" . $indexer->getTitle() . ' indexer process unknown error:</error>');
                $output->writeln("<error>" . $e->getMessage() . "</error>");
            }
        }

        $this->indexOperations->switchIndexer($index->getName(), $index->getIdentifier());

        $output->writeln(
            sprintf('<info>Index name: %s, index alias: %s</info>', $index->getName(), $index->getIdentifier())
        );
        $this->getIndexOperations()->switchIndexer($index->getName(), $index->getIdentifier());

        return $returnValue;
    }

    /**
     * Returns the ordered list of specified indexers.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *
     * @return IndexerInterface[]
     */
    private function getIndexers(InputInterface $input)
    {
        /** @var IndexerInterface[] */
        $availableIndexers = $this->getAvailableIndexers();
        $vsbridgeIndexers = [];

        // Handle requested indexers, if any were provided
        $requestedTypes = [];
        if ($input->getArgument(self::INPUT_KEY_INDEXERS)) {
            $requestedTypes = $input->getArgument(self::INPUT_KEY_INDEXERS);
            $requestedTypes = array_filter(array_map('trim', $requestedTypes), 'strlen');
        }

        if (empty($requestedTypes)) {
            // no indexers specific, set all available indexers
            $vsbridgeIndexers = $availableIndexers;
        } else {
            // Make sure requested indexers are valid vsbridge indexers
            $unsupportedTypes = array_diff($requestedTypes, array_keys($availableIndexers));
            if ($unsupportedTypes) {
                throw new \InvalidArgumentException(
                    "The following requested index types are not supported: '" . join("', '", $unsupportedTypes)
                    . "'." . PHP_EOL . 'Supported types: ' . join(", ", array_keys($availableIndexers))
                );
            }
            $vsbridgeIndexers = array_intersect_key($availableIndexers, array_flip($requestedTypes));
        }

        return $vsbridgeIndexers;
    }

    /**
     * Get all available indexers. Returns only indexers with 'vsbridge_'
     *
     * @return IndexerInterface[]
     */
    private function getAvailableIndexers()
    {
        /** @var IndexerInterface[] */
        $indexers = $this->getAllIndexers();

        $availableIndexers = [];
        foreach ($indexers as $indexerName => $indexer) {
            $indexId = $indexer->getId();

            if (substr($indexId, 0, 9) === 'vsbridge_' && !in_array($indexId, $this->excludeIndices)) {
                $availableIndexers[$indexerName] = $indexer;
            }
        }
        return $availableIndexers;
    }

    /**
     * @return StoreManagerInterface
     */
    private function getStoreManager()
    {
        if (null === $this->storeManager) {
            $this->storeManager = $this->getObjectManager()->get(StoreManagerInterface::class);
        }

        return $this->storeManager;
    }

    /**
     * @return StoreManager
     */
    private function getIndexerStoreManager()
    {
        if (null === $this->indexerStoreManager) {
            $this->indexerStoreManager = $this->getObjectManager()->get(StoreManager::class);
        }

        return $this->indexerStoreManager;
    }

    /**
     * @return IndexerRegistry
     */
    private function getIndexerRegistry()
    {
        if (null === $this->indexerRegistry) {
            $this->indexerRegistry = $this->getObjectManager()->get(IndexerRegistry::class);
        }

        return $this->indexerRegistry;
    }

    /**
     * @return IndexOperationInterface
     */
    private function getIndexOperations()
    {
        if (null === $this->indexOperations) {
            $this->indexOperations = $this->getObjectManager()->get(IndexOperationInterface::class);
        }

        return $this->indexOperations;
    }

    /**
     * Initiliaze object manager
     */
    private function initObjectManager()
    {
        $this->getObjectManager();
    }

    /**
     * Get list of options and arguments for the command
     *
     * @return mixed
     */
    public function getInputList()
    {
        return [
            new InputArgument(
                self::INPUT_KEY_INDEXERS,
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Space-separated list of index types or omit to apply to all vsbridge indexes.'
            ),
        ];
    }
}
