<?php

declare(strict_types=1);

namespace Divante\VsbridgeIndexerCore\Indexer;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class RebuildActionPool
 *
 * @api
 */
class RebuildActionPool
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var array
     */
    private $actions;

    /**
     * ActionPool constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $actions
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $actions
    ) {
        $this->actions = $actions;
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $entityType
     * @return RebuildActionInterface
     */
    public function getAction($entityType): ?RebuildActionInterface
    {
        if (isset($this->actions[$entityType])) {
            return $this->objectManager->get($this->actions[$entityType]);
        }

        return null;
    }
}
