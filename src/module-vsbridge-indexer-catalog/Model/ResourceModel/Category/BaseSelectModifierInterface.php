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

/**
 * Interface BaseSelectModifierInterface
 */
interface BaseSelectModifierInterface
{
    /**
     * Modify the select statement
     *
     * @param Select $select
     * @param int $storeId
     *
     * @return Select
     */
    public function execute(Select $select, int $storeId): Select;
}
