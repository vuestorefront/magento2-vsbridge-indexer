<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types = 1);

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product;

use Divante\VsbridgeIndexerCatalog\Model\ProductMetaData;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use Magento\Framework\DB\Select;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\Store;

/**
 * Class CustomOptionValues
 */
class CustomOptionValues
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
     * @param array $optionIds
     * @param int $storeId
     *
     * @return array
     */
    public function loadOptionValues(array $optionIds, int $storeId): array
    {
        $select = $this->getProductOptionSelect($optionIds, $storeId);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @param array $optionIds
     * @param int $storeId
     *
     * @return Select
     */
    private function getProductOptionSelect(array $optionIds, int $storeId): Select
    {
        $connection = $this->getConnection();
        $mainTableAlias = 'main_table';

        $select = $connection->select()->from(
            [$mainTableAlias => $this->resource->getTableName('catalog_product_option_type_value')]
        );

        $select->where($mainTableAlias.  '.option_id IN (?)', $optionIds);

        $select = $this->addTitleToResult($select, $storeId);
        $select = $this->addPriceToResult($select, $storeId);

        $select->order('sort_order ASC');
        $select->order('title ASC');

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
        $optionTitleTable = $this->resource->getTableName('catalog_product_option_type_title');
        $titleExpr = $this->getConnection()->getCheckSql(
            'store_value_title.title IS NULL',
            'default_value_title.title',
            'store_value_title.title'
        );

        $joinExpr = 'store_value_title.option_type_id = main_table.option_type_id AND ' .
            $this->getConnection()->quoteInto('store_value_title.store_id = ?', $storeId);
        $select->join(
            ['default_value_title' => $optionTitleTable],
            'default_value_title.option_type_id = main_table.option_type_id',
            ['default_title' => 'title']
        )->joinLeft(
            ['store_value_title' => $optionTitleTable],
            $joinExpr,
            ['store_title' => 'title', 'title' => $titleExpr]
        )->where(
            'default_value_title.store_id = ?',
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
        $optionTypeTable = $this->resource->getTableName('catalog_product_option_type_price');
        $priceExpr = $this->getConnection()->getCheckSql(
            'store_value_price.price IS NULL',
            'default_value_price.price',
            'store_value_price.price'
        );
        $priceTypeExpr = $this->getConnection()->getCheckSql(
            'store_value_price.price_type IS NULL',
            'default_value_price.price_type',
            'store_value_price.price_type'
        );

        $joinExprDefault = 'default_value_price.option_type_id = main_table.option_type_id AND ' .
            $this->getConnection()->quoteInto(
                'default_value_price.store_id = ?',
                Store::DEFAULT_STORE_ID
            );
        $joinExprStore = 'store_value_price.option_type_id = main_table.option_type_id AND ' .
            $this->getConnection()->quoteInto('store_value_price.store_id = ?', $storeId);
        $select->joinLeft(
            ['default_value_price' => $optionTypeTable],
            $joinExprDefault,
            ['default_price' => 'price', 'default_price_type' => 'price_type']
        )->joinLeft(
            ['store_value_price' => $optionTypeTable],
            $joinExprStore,
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
