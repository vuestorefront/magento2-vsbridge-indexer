<?php

namespace Divante\VsbridgeIndexerCore\Console\Command;

use Divante\VsbridgeIndexerCore\Console\Command\Rebuild\RebuildFactory;
use Divante\VsbridgeIndexerCore\Console\Command\Rebuild\Rebuild;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class IndexerReindexCommand
 */
class RebuildEsIndexCommand extends Command
{
    const INPUT_STORE = 'store';

    const INPUT_ALL_STORES = 'all';

    /** @var RebuildFactory */
    private $rebuildIndicesFactory;

    /** @var State */
    private $appState;

    /** @var ManagerInterface */
    private $eventManager;

    /**
     * RebuildEsIndexCommand constructor.
     * @param RebuildFactory $rebuildIndicesFactory
     * @param ManagerInterface $manager
     * @param State $appState
     */
    public function __construct(
        RebuildFactory $rebuildIndicesFactory,
        ManagerInterface $manager,
        State $appState
    ) {
        $this->appState = $appState;
        $this->eventManager = $manager;
        $this->rebuildIndicesFactory = $rebuildIndicesFactory;

        parent::__construct();
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
            'Reindex all allowed stores (base on vuestorefront configuration)'
        );

        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->setDecorated(true);

        try {
            $this->appState->getAreaCode();
        } catch (LocalizedException $exception) {
            $this->appState->setAreaCode(FrontNameResolver::AREA_CODE);
        }

        $store = $input->getOption(self::INPUT_STORE);
        $allStores = $input->getOption(self::INPUT_ALL_STORES);

        if (!$store && !$allStores) {
            $output->writeln(
                "<comment>Not enough information provided, nothing has been reindexed.
Try using --help for more information.</comment>"
            );

            return;
        }

        $this->eventManager->dispatch(
            'vsbridge_indexer_reindex_before',
            [
                'store' => $store,
                'allStores' => $allStores,
            ]
        );

        $rebuildIndicesCommand = $this->rebuildIndicesFactory->create($output);
        $rebuildIndicesCommand->execute($input->getOption(self::INPUT_STORE));

        $this->eventManager->dispatch(
            'vsbridge_indexer_reindex_after',
            [
                'store' => $store,
                'allStores' => $allStores,
            ]
        );
    }
}
