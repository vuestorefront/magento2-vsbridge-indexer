<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Marcin Dykas <mdykas@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types=1);

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManager;

/**
 * Class Downloadable
 */
class Downloadable
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * Downloadable constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param StoreManager $storeManager
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        StoreManager $storeManager
    )
    {
        $this->resource = $resourceConnection;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $productId
     * @param $storeId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDownloadableLinksByProductId($productId, $storeId)
    {
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();

        $select = $this->getConnection()->select()
            ->from(
                ['main_table' => $this->resource->getTableName('downloadable_link')],
                [
                    'id' => 'link_id',
                    'product_id' => 'product_id',
                    'sort_order' => 'sort_order',
                    'number_of_downloads' => 'number_of_downloads',
                    'is_shareable' => 'is_shareable',
                    'link_url' => 'link_url',
                    'link_file' => 'link_file',
                    'link_type' => 'link_type',
                    'sample_url' => 'sample_url',
                    'sample_file' => 'sample_file',
                    'sample_type' => 'sample_type',
                ]
            )
            ->where('product_id IN (?)', $productId);

        $this->addLinkTitleToResult($select, $storeId);
        $this->addLInkPriceToResult($select, $websiteId);

        $links = $this->getConnection()->fetchAll($select);

        return $links;
    }

    /**
     * @param $productId
     * @param $storeId
     * @return array
     */
    public function getDownloadableSamplesByProductId($productId, $storeId)
    {
        $select = $this->getConnection()->select()
            ->from(
                ['main_table' => $this->resource->getTableName('downloadable_sample')],
                [
                    'id' => 'sample_id',
                    'product_id' => 'product_id',
                    'sample_url' => 'sample_url',
                    'sample_file' => 'sample_file',
                    'sample_type' => 'sample_type',
                    'sort_order' => 'sort_order',
                ]
            )
            ->where('product_id IN (?)', $productId);

        $this->addSampleTitleToResult($select, $storeId);

        $samples = $this->getConnection()->fetchAll($select);

        return $samples;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }

    /**
     * @param Select $select
     * @param int $storeId
     * @return Select
     */
    private function addLinkTitleToResult(Select $select, $storeId = 0)
    {
        $ifNullDefaultTitle = $this->getConnection()->getIfNullSql('st.title', 'd.title');
        $select
            ->joinLeft(
                ['d' => $this->resource->getTableName('downloadable_link_title')],
                'd.link_id = main_table.link_id AND d.store_id = 0',
                ['default_title' => 'title']
            )->joinLeft(
                ['st' => $this->resource->getTableName('downloadable_link_title')],
                'st.link_id=main_table.link_id AND st.store_id = ' . (int)$storeId,
                [
                    'store_title' => 'title',
                    'title' => $ifNullDefaultTitle
                ]
            )->order('main_table.sort_order ASC')
            ->order('title ASC');
        return $select;
    }

    /**
     * @param Select $select
     * @param int $websiteId
     * @return Select
     */
    private function addLinkPriceToResult(Select $select, $websiteId = 0)
    {
        $ifNullDefaultPrice = $this->getConnection()->getIfNullSql('stp.price', 'dp.price');
        $select->joinLeft(
            ['dp' => $this->resource->getTableName('downloadable_link_price')],
            'dp.link_id=main_table.link_id AND dp.website_id = 0',
            ['default_price' => 'price']
        )->joinLeft(
            ['stp' => $this->resource->getTableName('downloadable_link_price')],
            'stp.link_id=main_table.link_id AND stp.website_id = ' . (int)$websiteId,
            ['website_price' => 'price', 'price' => $ifNullDefaultPrice]
        );

        return $select;
    }

    /**
     * Add sample title column to select
     *
     * @param int $storeId
     * @return $this
     */
    private function addSampleTitleToResult(Select $select, $storeId = 0)
    {
        $ifNullDefaultTitle = $this->getConnection()->getIfNullSql('st.title', 'd.title');
        $select->joinLeft(
            ['d' => $this->resource->getTableName('downloadable_sample_title')],
            'd.sample_id=main_table.sample_id AND d.store_id = 0',
            ['default_title' => 'title']
        )->joinLeft(
            ['st' => $this->resource->getTableName('downloadable_sample_title')],
            'st.sample_id=main_table.sample_id AND st.store_id = ' . (int)$storeId,
            ['store_title' => 'title', 'title' => $ifNullDefaultTitle]
        )->order(
            'main_table.sort_order ASC'
        )->order(
            'title ASC'
        );

        return $select;
    }
}
