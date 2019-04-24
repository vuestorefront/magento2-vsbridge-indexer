<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types = 1);

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product;

use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use Magento\Framework\DB\Select;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\Store;

/**
 * Class CustomOptions
 */
class CustomOptions
{

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var EntityAttribute
     */
    private $entityAttribute;

    /**
     * Gallery constructor.
     *
     * @param ResourceConnection $resourceModel
     * @param EntityAttribute $attribute
     */
    public function __construct(
        ResourceConnection $resourceModel,
        EntityAttribute $attribute
    ) {
        $this->entityAttribute = $attribute;
        $this->resource = $resourceModel;
    }

    /**
     * @param array $linkFieldIds
     * @param int $storeId
     *
     * @return array
     */
    public function loadProductOptions(array $linkFieldIds, int $storeId): array
    {
        $select = $this->getProductOptionSelect($linkFieldIds, $storeId);

        return $this->getConnection()->fetchAssoc($select);
    }

    /**
     * @param array $linkFieldIds
     * @param int $storeId
     *
     * @return Select
     */
    private function getProductOptionSelect(array $linkFieldIds, int $storeId): Select
    {
        $connection = $this->getConnection();
        $mainTableAlias = 'main_table';

        $select = $connection->select()->from(
            [$mainTableAlias => $this->resource->getTableName('catalog_product_option')]
        );

        $select->where($mainTableAlias.  '.product_id IN (?)', $linkFieldIds);

        $select = $this->addTitleToResult($select, $storeId);
        $select = $this->addPriceToResult($select, $storeId);

        return $select;
    }

    /**
     * @param Select $select
     * @param int $storeId
     *
     * @return Select
     */
    private function addTitleToResult(Select $select, int $storeId): Select
    {
        $productOptionTitleTable = $this->resource->getTableName('catalog_product_option_title');
        $connection = $this->getConnection();
        $titleExpr = $connection->getCheckSql(
            'store_option_title.title IS NULL',
            'default_option_title.title',
            'store_option_title.title'
        );

        $select->join(
            ['default_option_title' => $productOptionTitleTable],
            'default_option_title.option_id = main_table.option_id',
            ['default_title' => 'title']
        )->joinLeft(
            ['store_option_title' => $productOptionTitleTable],
            'store_option_title.option_id = main_table.option_id AND ' . $connection->quoteInto(
                'store_option_title.store_id = ?',
                $storeId
            ),
            [
                'store_title' => 'title',
                'title' => $titleExpr
            ]
        )->where(
            'default_option_title.store_id = ?',
            Store::DEFAULT_STORE_ID
        );

        return $select;
    }

    /**
     * @param Select $select
     * @param int $storeId
     *
     * @return Select
     */
    private function addPriceToResult(Select $select, int $storeId): Select
    {
        $productOptionPriceTable = $this->resource->getTableName('catalog_product_option_price');
        $connection = $this->getConnection();
        $priceExpr = $connection->getCheckSql(
            'store_option_price.price IS NULL',
            'default_option_price.price',
            'store_option_price.price'
        );
        $priceTypeExpr = $connection->getCheckSql(
            'store_option_price.price_type IS NULL',
            'default_option_price.price_type',
            'store_option_price.price_type'
        );

        $select->joinLeft(
            ['default_option_price' => $productOptionPriceTable],
            'default_option_price.option_id = main_table.option_id AND ' . $connection->quoteInto(
                'default_option_price.store_id = ?',
                Store::DEFAULT_STORE_ID
            ),
            [
                'default_price' => 'price',
                'default_price_type' => 'price_type'
            ]
        )->joinLeft(
            ['store_option_price' => $productOptionPriceTable],
            'store_option_price.option_id = main_table.option_id AND ' . $connection->quoteInto(
                'store_option_price.store_id = ?',
                $storeId
            ),
            [
                'store_price' => 'price',
                'store_price_type' => 'price_type',
                'price' => $priceExpr,
                'price_type' => $priceTypeExpr
            ]
        );

        return $select;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
}
