<?php

declare(strict_types=1);

/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */
namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category;

use Magento\Framework\DB\Select;
use Magento\Framework\Exception\InputException;

/**
 * Class CompositeBaseSelectModifier
 */
class CompositeBaseSelectModifier implements BaseSelectModifierInterface
{
    /**
     * @var BaseSelectModificatorInterface[]
     */
    private $baseSelectModifiers;

    /**
     * @param BaseSelectModificatorInterface[] $baseSelectModifiers
     * @throws InputException
     */
    public function __construct(array $baseSelectModifiers)
    {
        foreach ($baseSelectModifiers as $baseSelectModifier) {
            if (!$baseSelectModifier instanceof BaseSelectModifierInterface) {
                throw new InputException(
                    __(
                        'Modifier %1 doesn\'t implement BaseSelectModifierInterface',
                        get_class($baseSelectModifier)
                    )
                );
            }
        }

        $this->baseSelectModifiers = $baseSelectModifiers;
    }

    /**
     * @param Select $select
     * @param int $storeId
     *
     * @return Select
     */
    public function execute(Select $select, int $storeId): Select
    {
        foreach ($this->baseSelectModifiers as $baseSelectModifier) {
            $select = $baseSelectModifier->execute($select, $storeId);
        }

        return $select;
    }
}
