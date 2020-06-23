<?php
/**
 * Copyright Divante Sp. z o.o.
 * See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Console\Command;

use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Indexer\Console\Command\AbstractIndexerCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ResetEsIndexCommand
 */
class ResetEsIndexCommand extends AbstractIndexerCommand
{
    const VS_INDEXER_PREFIX = 'vsbridge_';

    /**
     * ResetEsIndexCommand constructor.
     *
     * @param  ObjectManagerFactory  $objectManagerFactory
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
        $this->setName('vsbridge:reset')
            ->setDescription('Resets vsbridge indices status to invalid');

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->invalidateIndices($output);
    }

    /**
     * @param OutputInterface $output
     */
    private function invalidateIndices(OutputInterface $output)
    {
        foreach ($this->getIndexers() as $indexer) {
            try {
                $indexer->getState()
                    ->setStatus(\Magento\Framework\Indexer\StateInterface::STATUS_INVALID)
                    ->save();
                $output->writeln($indexer->getTitle() . ' indexer has been invalidated.');
            } catch (LocalizedException $e) {
                //catch exception
                $output->writeln("<error>" . $e->getMessage() . "</error>");
            }
        }
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

            if (substr($indexId, 0, 9) === self::VS_INDEXER_PREFIX) {
                $vsbridgeIndexers[] = $indexer;
            }
        }

        return $vsbridgeIndexers;
    }
}
