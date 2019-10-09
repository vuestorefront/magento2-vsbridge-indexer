<?php
/**
 * @package  Divante\VsbridgeIndexerCore
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Console\Command;

use Divante\VsbridgeIndexerCatalog\Model\Indexer\ProductCategoryProcessor;
use Divante\VsbridgeIndexerCore\Indexer\StoreManager;
use Divante\VsbridgeIndexerCore\Index\IndexOperations;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Indexer\Console\Command\AbstractIndexerCommand;
use Magento\Store\Api\Data\StoreInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class IndexerReindexCommand
 */
class RebuildEsIndexCommand extends AbstractIndexerCommand
{
    const INPUT_STORE = 'store';
    const INPUT_ALL_STORES = 'all';
    const INPUT_DELETE_INDEX = 'delete-index';

    const INDEX_IDENTIFIER = 'vue_storefront_catalog';

    /**
     * @var IndexOperations
     */
    private $indexOperations;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * RebuildEsIndexCommand constructor.
     *
     * @param ObjectManagerFactory $objectManagerFactory
     * @param IndexOperations $indexOperations
     * @param StoreManager $storeManager
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        IndexOperations $indexOperations,
        StoreManager $storeManager
    ) {
        $this->indexOperations = $indexOperations;
        $this->storeManager = $storeManager;

        parent::__construct($objectManagerFactory);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('vsbridge:reindex')
            ->setDescription('Rebuild indexer in ES.');

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


        $this->addOption(
            self::INPUT_DELETE_INDEX,
            null,
            InputOption::VALUE_NONE,
            'Delete previous index and create new one (with new mapping)'
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
        $deleteIndex = $input->getOption(self::INPUT_DELETE_INDEX);

        if ($storeId) {
            $stores = $this->storeManager->getStores($storeId);

            if (!empty($stores)) {
                /** @var \Magento\Store\Api\Data\StoreInterface $store */
                $store = $stores[0];
                $output->writeln("<info>Reindexing all VS indexes for store " . $store->getName() . "...</info>");

                $returnValue = $this->reindexStore($store, $deleteIndex, $output);

                $output->writeln("<info>Reindexing has completed!</info>");

                return $returnValue;
            }
        } elseif ($allStores) {
            $output->writeln("<info>Reindexing all stores...</info>");
            $returnValues = [];

            /** @var \Magento\Store\Api\Data\StoreInterface $store */
            foreach ($this->storeManager->getStores() as $store) {
                $output->writeln("<info>Reindexing store " . $store->getName() . "...</info>");
                $returnValues[] = $this->reindexStore($store, $deleteIndex, $output);
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
     * @param bool $deleteIndex
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    private function reindexStore(StoreInterface $store, bool $deleteIndex, OutputInterface $output)
    {
        if ($deleteIndex) {
            $output->writeln("<comment>Deleting and recreating the index first...</comment>");
            $this->indexOperations->deleteIndex(self::INDEX_IDENTIFIER, $store);
            $this->indexOperations->createIndex(self::INDEX_IDENTIFIER, $store);
        }

        $returnValue = Cli::RETURN_FAILURE;

        foreach ($this->getIndexers() as $indexer) {
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

        return $returnValue;
    }

    /**
     * @return IndexerInterface[]
     */
    private function getIndexers()
    {
        /** @var IndexerInterface[] */
        $indexers = $this->getAllIndexers();

        unset($indexers[ProductCategoryProcessor::INDEXER_ID]);
        $vsbridgeIndexers = [];

        foreach ($indexers as $indexer) {
            if (substr($indexer->getId(), 0, 9) === 'vsbridge_') {
                $vsbridgeIndexers[] = $indexer;
            }
        }

        return $vsbridgeIndexers;
    }

    /**
     * Initiliaze object manager
     */
    private function initObjectManager()
    {
        $this->getObjectManager();
    }
}
