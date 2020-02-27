<?php

declare(strict_types=1);

/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product;

use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class WebsiteSelectModifier
 */
class WebsiteSelectModifier implements BaseSelectModifierInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * WebsiteSelectModifier constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Select $select, int $storeId): Select
    {
        $connection = $select->getConnection();
        $websiteId = $this->getWebsiteId($storeId);
        $indexTable = $this->resourceConnection->getTableName('catalog_product_website');

        $conditions = [sprintf('websites.product_id = %s.entity_id', Product::MAIN_TABLE_ALIAS)];
        $conditions[] = $connection->quoteInto('websites.website_id = ?', $websiteId);

        $select->join(['websites' => $indexTable], join(' AND ', $conditions), []);

        return $select;
    }

    /**
     * Retrieve WebsiteId for given store
     *
     * @param int $storeId
     *
     * @return int
     * @throws NoSuchEntityException
     */
    private function getWebsiteId(int $storeId): int
    {
        $store = $this->storeManager->getStore($storeId);

        return (int) $store->getWebsiteId();
    }
}
