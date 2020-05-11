<?php

declare(strict_types=1);

/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category;

use Divante\VsbridgeIndexerCatalog\Model\CategoryMetaData;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class StoreSelectModificator
 */
class StoreSelectModifier implements BaseSelectModifierInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * StoreSelectModificator constructor.
     *
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Modify the select statement
     *
     * @param Select $select
     * @return Select
     */
    public function execute(Select $select, int $storeId): Select
    {
        $store = $this->storeManager->getStore($storeId);
        $connection = $select->getConnection();

        $rootId = Category::TREE_ROOT_ID;
        $rootCatIdExpr = $connection->quote(sprintf("%s/%s", $rootId, $store->getRootCategoryId()));
        $catIdExpr = $connection->quote(sprintf("%s/%s/%%", $rootId, $store->getRootCategoryId()));
        $whereCondition = sprintf("path = %s OR path like %s", $rootCatIdExpr, $catIdExpr);

        $select->where($whereCondition);

        return $select;
    }
}
