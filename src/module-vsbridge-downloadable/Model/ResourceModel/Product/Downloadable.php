<?php
/**
 * Copyright Divante Sp. z o.o.
 * See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types=1);

namespace Divante\VsbridgeDownloadable\Model\ResourceModel\Product;

use Divante\VsbridgeIndexerCatalog\Model\ProductMetaData;
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
     * @var ProductMetaData
     */
    private $productMetaData;

    /**
     * @var array
     */
    private $products;

    /**
     * Downloadable constructor.
     *
     * @param ProductMetaData $productMetaData
     * @param ResourceConnection $resourceConnection
     * @param StoreManager $storeManager
     */
    public function __construct(
        ProductMetaData $productMetaData,
        ResourceConnection $resourceConnection,
        StoreManager $storeManager
    ) {
        $this->resource = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->productMetaData = $productMetaData;
    }

    /**
     * Set Products
     *
     * @param array $products
     */
    public function setProducts(array $products)
    {
        $this->products = [];
        $linkField = $this->productMetaData->get()->getLinkField();

        foreach ($products as $product) {
            if ($product['type_id'] === 'downloadable') {
                $this->products[$product[$linkField]] = $product;
            }
        }
    }

    /**
     * @param int $storeId
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDownloadableLinksByProductId(int $storeId): array
    {
        $productId = array_keys($this->products);

        if (empty($productId)) {
            return [];
        }

        $websiteId = (int) $this->storeManager->getStore($storeId)->getWebsiteId();

        $select = $this->buildDownloadLinkSelect($productId);
        $this->addLinkTitleToResult($select, $storeId);
        $this->addLInkPriceToResult($select, $websiteId);

        $links = $this->getConnection()->fetchAll($select);

        return $this->groupResultByProduct($links);
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function getDownloadableSamplesByProductId(int $storeId): array
    {
        $productId = array_keys($this->products);

        if (empty($productId)) {
            return [];
        }

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

        return $this->groupResultByProduct($samples);
    }

    /**
     * Build base Download Link Select
     *
     * @param array $productId
     *
     * @return Select
     */
    private function buildDownloadLinkSelect(array $productId): Select
    {
        return $this->getConnection()->select()
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
    }

    /**
     * Group result by product
     *
     * @param array $sourceData
     *
     * @return array
     */
    private function groupResultByProduct(array $sourceData): array
    {
        $groupedResultByProduct =  [];
        $entityField = $this->productMetaData->get()->getIdentifierField();

        foreach ($sourceData as $sample) {
            $linkFieldId = $sample['product_id'];
            $productId = $this->products[$linkFieldId][$entityField];
            $groupedResultByProduct[$productId][] = $sample;
        }

        return $groupedResultByProduct;
    }

    /**
     * @param Select $select
     * @param int $storeId
     *
     * @return Select
     */
    private function addLinkTitleToResult(Select $select, int $storeId): Select
    {
        $ifNullDefaultTitle = $this->getConnection()->getIfNullSql('st.title', 'd.title');
        $select
            ->joinLeft(
                ['d' => $this->resource->getTableName('downloadable_link_title')],
                'd.link_id = main_table.link_id AND d.store_id = 0',
                ['default_title' => 'title']
            )->joinLeft(
                ['st' => $this->resource->getTableName('downloadable_link_title')],
                'st.link_id=main_table.link_id AND st.store_id = ' . $storeId,
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
     *
     * @return Select
     */
    private function addLinkPriceToResult(Select $select, int $websiteId): Select
    {
        $ifNullDefaultPrice = $this->getConnection()->getIfNullSql('stp.price', 'dp.price');
        $select->joinLeft(
            ['dp' => $this->resource->getTableName('downloadable_link_price')],
            'dp.link_id=main_table.link_id AND dp.website_id = 0',
            ['default_price' => 'price']
        )->joinLeft(
            ['stp' => $this->resource->getTableName('downloadable_link_price')],
            'stp.link_id=main_table.link_id AND stp.website_id = ' . $websiteId,
            ['website_price' => 'price', 'price' => $ifNullDefaultPrice]
        );

        return $select;
    }

    /**
     * Add sample title column to select
     *
     * @param Select $select
     * @param int $storeId
     *
     * @return Select
     */
    private function addSampleTitleToResult(Select $select, int $storeId): Select
    {
        $ifNullDefaultTitle = $this->getConnection()->getIfNullSql('st.title', 'd.title');
        $select->joinLeft(
            ['d' => $this->resource->getTableName('downloadable_sample_title')],
            'd.sample_id=main_table.sample_id AND d.store_id = 0',
            ['default_title' => 'title']
        )->joinLeft(
            ['st' => $this->resource->getTableName('downloadable_sample_title')],
            'st.sample_id=main_table.sample_id AND st.store_id = ' . $storeId,
            ['store_title' => 'title', 'title' => $ifNullDefaultTitle]
        )->order(
            'main_table.sort_order ASC'
        )->order(
            'title ASC'
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
