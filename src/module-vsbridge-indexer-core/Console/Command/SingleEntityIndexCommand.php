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
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Indexer\Console\Command\AbstractIndexerCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class SingleEntityIndexCommand
 */
class SingleEntityIndexCommand extends AbstractIndexerCommand
{
    const INPUT_STORE = 'store';

    const INPUT_INDEXER_CODE = 'index';

    const INPUT_ENTITY_ID = 'id';

    /**
     * @var StoreManager
     */
    private $indexerStoreManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * RebuildEsIndexCommand constructor.
     *
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(ObjectManagerFactory $objectManagerFactory)
    {
        parent::__construct($objectManagerFactory);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('vsbridge:index')
            ->setDescription(
                'Update single entity in ES (product, category, attribute,  etc..). Useful tool for testing new data.'
            );

        $this->setDefinition($this->getInputList());

        parent::configure();
    }

    /**
     * Get list of options and arguments for the command
     *
     * @return array
     */
    private function getInputList()
    {
        return [
            new InputArgument(
                self::INPUT_INDEXER_CODE,
                InputArgument::REQUIRED,
                'Indexer code'
            ),
            new InputArgument(
                self::INPUT_STORE,
                InputArgument::REQUIRED,
                'Store ID or Store Code'
            ),
            new InputArgument(
                self::INPUT_ENTITY_ID,
                InputArgument::REQUIRED,
                'Entity id'
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initObjectManager();
        $output->setDecorated(true);

        $storeId = $input->getArgument(self::INPUT_STORE);
        $index = $input->getArgument(self::INPUT_INDEXER_CODE);
        $id = $input->getArgument(self::INPUT_ENTITY_ID);

        $store = $this->getStoreManager()->getStore($storeId);
        $this->getIndexerStoreManager()->override([$store]);
        $indexer = $this->getIndex($index);

        if ($indexer) {
            $message = "\nIndex: " . $indexer->getTitle() .
                "\nStore: " . $store->getName() .
                "\nID: " . $id;
            $output->writeln("<info>Indexing... $message</info>");
            $indexer->reindexRow($id);
        } else {
            $output->writeln("<info>Index with code: $index hasn't been found. </info>");
        }
    }

    /**
     * @return IndexerInterface
     */
    private function getIndex($code)
    {
        /** @var IndexerInterface[] */
        $indexers = $this->getAllIndexers();
        $vsbridgeIndexers = [];

        foreach ($indexers as $indexer) {
            $indexId = $indexer->getId();

            if ($code === $indexId) {
                return $indexer;
            }
        }

        return null;
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
