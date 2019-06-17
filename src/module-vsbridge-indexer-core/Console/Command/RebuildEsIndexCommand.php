<?php
/**
 * @package  Divante\VsbridgeIndexerCore
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Console\Command;

use Divante\VsbridgeIndexerCore\Indexer\StoreManager;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\App\ObjectManagerFactory;
use Divante\VsbridgeIndexerCatalog\Model\Indexer\ProductCategoryProcessor;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Input\InputArgument;
use Divante\VsbridgeIndexerCore\Index\IndexOperations;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Indexer\IndexerRegistry;

/**
 * Class IndexerReindexCommand
 */
class RebuildEsIndexCommand extends Command
{
    const STORE_ID = 'store';

    const INDEX_IDENTIFIER = 'vue_storefront_catalog';

    /**
     * @var \Magento\Indexer\Model\Indexer\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var IndexOperations
     */
    private $indexOperations;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * Constructor
     *
     * @param \Magento\Indexer\Model\Indexer\CollectionFactory|null $collectionFactory
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        IndexOperations\Proxy $indexOperations,
        StoreManagerInterface\Proxy $storeManager,
        \Magento\Indexer\Model\Indexer\CollectionFactory\Proxy $collectionFactory
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->collectionFactory = $collectionFactory;
        $this->indexOperations = $indexOperations;
        $this->storeManager = $storeManager;
        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('vsbridge:reindex')
            ->setDescription('Rebuild indexer. Delete index and create index with new mapping');

        $this->addArgument(self::STORE_ID, InputArgument::REQUIRED, 'Store Id');

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasArgument(self::STORE_ID)) {
            $storeId = (int)$input->getArgument(self::STORE_ID);
            $store = $this->storeManager->getStore($storeId);
            $this->indexOperations->deleteIndex(self::INDEX_IDENTIFIER, $store);
            $this->indexOperations->createIndex(self::INDEX_IDENTIFIER, $store);

            $returnValue = Cli::RETURN_FAILURE;

            foreach ($this->getIndexers($input) as $indexer) {
                try {
                    $startTime = microtime(true);

                    $indexer->reindexAll();

                    $resultTime = microtime(true) - $startTime;
                    $output->writeln(
                        $indexer->getTitle() . ' index has been rebuilt successfully in ' . gmdate('H:i:s', $resultTime)
                    );
                    $returnValue = Cli::RETURN_SUCCESS;
                } catch (LocalizedException $e) {
                    $output->writeln($e->getMessage());
                } catch (\Exception $e) {
                    $output->writeln($indexer->getTitle() . ' indexer process unknown error:');
                    $output->writeln($e->getMessage());
                }
            }

            return $returnValue;
        }
    }

    /**
     * @return IndexerInterface[]
     */
    protected function getIndexers(InputInterface $input)
    {
        /** @var IndexerInterface[] */
        $indexers = $this->collectionFactory->create()->getItems();
        unset($indexers[ProductCategoryProcessor::INDEXER_ID]);
        $vsbridgeIndexers = [];

        foreach ($indexers as $indexer) {
            if (substr($indexer->getId(), 0, 9) === 'vsbridge_') {
                $vsbridgeIndexers[] = $indexer;
            }
        }

        return $vsbridgeIndexers;
    }
}
