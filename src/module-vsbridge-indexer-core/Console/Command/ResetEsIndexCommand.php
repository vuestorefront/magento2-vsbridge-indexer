<?php

/**
 * Copyright Divante Sp. z o.o.
 * See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Console\Command;

use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Indexer\Console\Command\AbstractIndexerCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ResetEsIndexCommand
 */
class ResetEsIndexCommand extends AbstractIndexerCommand {

    const VSINDEX_PREFIX = 'vsbridge_';

    /**
     * ResetEsIndexCommand constructor.
     *
     * @param  ObjectManagerFactory  $objectManagerFactory
     * @param  array  $excludeIndices
     */
    public function __construct(
            ObjectManagerFactory $objectManagerFactory
    ) {
        parent::__construct($objectManagerFactory);
    }

    /**
     * @inheritdoc
     */
    protected function configure() {
        $this->setName('vsbridge:resetindex')
                ->setDescription('Reset indexer.');

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        //invalidate all indexes
        $this->getInvalidIndices();
    }

    /**
     * @return array
     */
    private function getInvalidIndices() {
        foreach ($this->getIndexers() as $indexer) {
            if ($indexer->isWorking()) {
                try {
                    $indexer->getState()
                            ->setStatus(\Magento\Framework\Indexer\StateInterface::STATUS_INVALID)
                            ->save();
                    $output->writeln($indexer->getTitle() . ' indexer has been invalidated.');
                } catch (LocalizedException $e) {
                    $output->writeln($e->getMessage());
                } catch (\Exception $e) {
                    $output->writeln($indexer->getTitle() . ' indexer process unknown error:');
                    $output->writeln($e->getMessage());
                }
            }
        }
    }

    /**
     * @return IndexerInterface[]
     */
    private function getIndexers() {
        /** @var IndexerInterface[] */
        $indexers = $this->getAllIndexers();
        $vsbridgeIndexers = [];

        foreach ($indexers as $indexer) {
            $indexId = $indexer->getId();

            if (substr($indexId, 0, 9) === self::VSINDEX_PREFIX) {
                $vsbridgeIndexers[] = $indexer;
            }
        }

        return $vsbridgeIndexers;
    }

}
