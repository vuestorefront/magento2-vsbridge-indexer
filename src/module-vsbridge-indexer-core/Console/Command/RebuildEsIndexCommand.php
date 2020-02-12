<?php
/**
 * @package  Divante\VsbridgeIndexerCore
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Console\Command;

use Divante\VsbridgeIndexerCore\Indexer\StoreManager;

use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Indexer\Console\Command\AbstractIndexerCommand;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class IndexerReindexCommand
 */
class RebuildEsIndexCommand extends AbstractIndexerCommand
{
    const INPUT_STORE = 'store';

    const INPUT_ALL_STORES = 'all';

    /**
     * @var StoreManager
     */
    private $indexerStoreManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

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

        if (!$storeId && !$allStores) {
            $output->writeln(
                "<comment>Not enough information provided, nothing has been reindexed. Try using --help for more information.</comment>"
            );
        } else {
            $this->reindex($output, $storeId, $allStores);
        }
    }

    /**
     * @return array
     */
    private function getInvalidIndices()
    {
        $invalid = [];

        foreach ($this->getIndexers() as $indexer) {
            if ($indexer->isWorking()) {
                $invalid[] = $indexer->getTitle();
            }
        }

        return $invalid;
    }

    /***
     * @param OutputInterface $output
     * @param $storeId
     * @param $allStores
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function reindex(OutputInterface $output, $storeId, $allStores)
    {
        if ($storeId) {
            $store = $this->getStoreManager()->getStore($storeId);
            $output->writeln("<info>Reindexing all VS indexes for store " . $store->getName() . "...</info>");

            $returnValue = $this->reindexStore($store, $output);

            $output->writeln("<info>Reindexing has completed!</info>");

            return $returnValue;

        } elseif ($allStores) {
            $output->writeln("<info>Reindexing all stores...</info>");
            $returnValues = [];

            /** @var \Magento\Store\Api\Data\StoreInterface $store */
            foreach ($this->getStoreManager()->getStores() as $store) {
                $output->writeln("<info>Reindexing store " . $store->getName() . "...</info>");
                $returnValues[] = $this->reindexStore($store, $output);
            }

            $output->writeln("<info>All stores have been reindexed!</info>");

            // If failure returned in any store return failure now
            return in_array(Cli::RETURN_FAILURE, $returnValues) ? Cli::RETURN_FAILURE : Cli::RETURN_SUCCESS;
        }
    }

    /**
     * Reindex each vsbridge index for the specified store
     *
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    private function reindexStore(StoreInterface $store, OutputInterface $output)
    {
        $this->getIndexerStoreManager()->setLoadedStores([$store]);

        $returnValue = Cli::RETURN_FAILURE;

        foreach ($this->getIndexers() as $indexer) {
            if ($indexer->isWorking()) {
                $message = $indexer->getTitle() . ' can\'t be executed. Change indexer status manually to VALID.';
                $output->writeln("<info>WARNING: $message");
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

        return $returnValue;
    }

    /**
     * @return IndexerInterface[]
     */
    private function getIndexers()
    {
        /** @var IndexerInterface[] */
        $indexers = $this->getAllIndexers();
        $vsbridgeIndexers = [];

        foreach ($indexers as $indexer) {
            $indexId = $indexer->getId();

            if (substr($indexId, 0, 9) === 'vsbridge_' && !in_array($indexId, $this->excludeIndices)) {
                $vsbridgeIndexers[] = $indexer;
            }
        }

        return $vsbridgeIndexers;
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
     * Initiliaze object manager
     */
    private function initObjectManager()
    {
        $this->getObjectManager();
    }
}
