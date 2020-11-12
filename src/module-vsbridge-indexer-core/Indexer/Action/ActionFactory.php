<?php

namespace Divante\VsbridgeIndexerCore\Indexer\Action;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for indexer action
 */
class ActionFactory
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var array */
    private $actions = [];

    /**
     * ActionFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param array $actions
     */
    public function __construct(ObjectManagerInterface $objectManager, array $actions)
    {
        $this->actions = $actions;
        $this->objectManager = $objectManager;
    }

    /**
     * Return Action class base on action type: full, rows
     * @param string $actionType
     * @param string $entityType
     *
     * @return AbstractAction
     */
    public function create(string $actionType, string $entityType): AbstractAction
    {
        $actionFactoryClass = $this->actions[$actionType];

        return $this->objectManager->create($actionFactoryClass, ['typeName' => $entityType]);
    }
}
