<?php

namespace Divante\VsbridgeIndexerCore\Console\Command\Rebuild;

use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Factory for Rebuild class
 */
class RebuildFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * RebuildFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param OutputInterface $output
     * @return Rebuild
     */
    public function create(OutputInterface $output): Rebuild
    {
        return $this->objectManager->create(Rebuild::class, ['output' => $output]);
    }
}
