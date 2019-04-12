<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Divante\VsbridgeIndexerCatalog\Model\ProductMetaData;
use Magento\Catalog\Model\Indexer\Product\Price\PriceTableResolver;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;


/**
 * Class Prices
 */
class Prices
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductMetaData
     */
    private $productMetaData;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\PriceTableResolver
     */
    private $priceTableResolver;

    /**
     * @var \Magento\Framework\Indexer\DimensionFactory
     */
    private $dimensionFactory;

    /**
     * Prices constructor.
     *
     * @param ResourceConnection $resourceModel
     * @param StoreManagerInterface $storeManager
     * @param ProductMetaData $productMetaData
     */
    public function __construct(
        ResourceConnection $resourceModel,
        StoreManagerInterface $storeManager,
        ProductMetaData $productMetaData,
        PriceTableResolver $priceTableResolver,
        DimensionFactory $dimensionFactory
    ) {
        $this->resource = $resourceModel;
        $this->storeManager = $storeManager;
        $this->productMetaData = $productMetaData;
        $this->priceTableResolver = $priceTableResolver;
        $this->dimensionFactory = $dimensionFactory;
    }

    /**
     * @param int   $storeId
     * @param array $productIds
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadPriceData($storeId, array $productIds)
    {
        $entityIdField = $this->productMetaData->get()->getIdentifierField();
        $websiteId = $this->getStore($storeId)->getWebsiteId();

        // only default customer group Id is supported now
        $customerGroupId = 0;

        $priceIndexTableName = $this->priceTableResolver->resolve(
            'catalog_product_index_price',
            [
                $this->dimensionFactory->create(
                    WebsiteDimensionProvider::DIMENSION_NAME,
                    (string)$websiteId
                ),
                $this->dimensionFactory->create(
                    CustomerGroupDimensionProvider::DIMENSION_NAME,
                    (string)$customerGroupId
                ),
            ]
        );

        $select = $this->getConnection()->select()
            ->from(
                ['p' => $priceIndexTableName],
                [
                    $entityIdField,
                    'price',
                    'final_price',
                ]
            )
            ->where('p.customer_group_id = ?', $customerGroupId)
            ->where('p.website_id = ?', $websiteId)
            ->where("p.$entityIdField IN (?)", $productIds);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @param int $storeId
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getStore($storeId)
    {
        return $this->storeManager->getStore($storeId);
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
}
