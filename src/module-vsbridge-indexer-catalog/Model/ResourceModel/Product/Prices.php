<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types = 1);

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
     * @var array
     */
    private $priceIndexTableName = [];

    /**
     * Prices constructor.
     *
     * @param ResourceConnection $resourceModel
     * @param StoreManagerInterface $storeManager
     * @param ProductMetaData $productMetaData
     * @param PriceTableResolver $priceTableResolver
     * @param DimensionFactory $dimensionFactory
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
    public function loadPriceData(int $storeId, array $productIds): array
    {
        $entityIdField = $this->productMetaData->get()->getIdentifierField();
        $websiteId = (int)$this->getStore($storeId)->getWebsiteId();

        // only default customer group Id is supported now
        $customerGroupId = 0;
        $priceIndexTableName = $this->getPriceIndexTableName($websiteId, $customerGroupId);

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
     * @param int $websiteId
     * @param int $customerGroupId
     *
     * @return string
     */
    private function getPriceIndexTableName(int $websiteId, int $customerGroupId): string
    {
        $key = $websiteId . '_' . $customerGroupId;

        if (!isset($this->priceIndexTableName[$key])) {
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
            
            $this->priceIndexTableName[$key] = (string)$priceIndexTableName;
        }

        return $this->priceIndexTableName[$key];
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
