<?php
/**
 * @package  Divante\VsbridgeIndexerCore
 * @author Nagaraja Kharvi <nagrgk@gmail.com>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Console\Command;

use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Indexer\Console\Command\AbstractIndexerCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class IndexerReindexCommand
 */
class ResetEsIndexCommand extends AbstractIndexerCommand
{
    /**
     * RebuildEsIndexCommand constructor.
     *
     * @param  ObjectManagerFactory  $objectManagerFactory
     * @param  ManagerInterface  $eventManager
     * @param  array  $excludeIndices
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        ManagerInterface $eventManager
    ) {
        parent::__construct($objectManagerFactory);
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('vsbridge:resetindex')
            ->setDescription('Reset indexer.');

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //invalidate all indexes
        $invalidIndices = $this->getInvalidIndices();
    }

    /**
     * @return array
     */
    private function getInvalidIndices()
    {
        $invalid = [];

        foreach ($this->getIndexers() as $indexer) {
            if ($indexer->isWorking()) {
                try {
                    $indexer->getState()
                        ->setStatus(\Magento\Framework\Indexer\StateInterface::STATUS_INVALID)
                        ->save();
		    $output->writeln("\n" . $indexer->getTitle() . "\n");
                } catch (LocalizedException $e) {
                    //catch exception
                    $output->writeln("<error>" . $e->getMessage() . "</error>");
                }
            }
        }

        return $invalid;
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

            if (substr($indexId, 0, 9) === 'vsbridge_') {
                $vsbridgeIndexers[] = $indexer;
            }
        }

        return $vsbridgeIndexers;
    }
}

