<?php declare(strict_types = 1);
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogRule\Model\ResourceModel\Rule\Product\Price as CatalogRulePrice;

use Divante\VsbridgeIndexerCatalog\Api\CatalogConfigurationInterface;
use Divante\VsbridgeIndexerCatalog\Model\ProductMetaData;
use Divante\VsbridgeIndexerCatalog\Model\Product\PriceTableResolverProxy;

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
     * @var CatalogConfigurationInterface
     */
    private $settings;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductMetaData
     */
    private $productMetaData;

    /**
     * @var PriceTableResolverProxy
     */
    private $priceTableResolver;

    /**
     * @var CatalogRulePrice
     */
    private $catalogPriceResourceModel;

    /**
     * Prices constructor.
     *
     * @param ResourceConnection $resourceModel
     * @param StoreManagerInterface $storeManager
     * @param ProductMetaData $productMetaData
     * @param CatalogConfigurationInterface $catalogSettings
     * @param CatalogRulePrice $catalogPriceResourceModel
     * @param PriceTableResolverProxy $priceTableResolver
     */
    public function __construct(
        ResourceConnection $resourceModel,
        StoreManagerInterface $storeManager,
        ProductMetaData $productMetaData,
        CatalogConfigurationInterface $catalogSettings,
        CatalogRulePrice $catalogPriceResourceModel,
        PriceTableResolverProxy $priceTableResolver
    ) {
        $this->resource = $resourceModel;
        $this->storeManager = $storeManager;
        $this->productMetaData = $productMetaData;
        $this->priceTableResolver = $priceTableResolver;
        $this->settings = $catalogSettings;
        $this->catalogPriceResourceModel = $catalogPriceResourceModel;
    }

    /**
     * Only default customer Group ID (0) is supported now
     * @param int $storeId
     * @param array $productIds
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadPriceData(int $storeId, array $productIds): array
    {
        $entityIdField = $this->productMetaData->get()->getIdentifierField();
        $websiteId = (int)$this->getStore($storeId)->getWebsiteId();

        // Only default customer Group ID (0) is supported now
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

        $prices = $this->getConnection()->fetchAssoc($select);

        if ($this->settings->useCatalogRules()) {
            $catalogPrices = $this->getCatalogRulePrices($websiteId, $productIds);

            foreach ($catalogPrices as $productId => $finalPrice) {
                $priceIndexerPrice =
                    $prices[$productId]['final_price'] ?? $prices[$productId]['final_price'] ?? $finalPrice;
                $prices[$productId]['final_price'] = min($finalPrice, $priceIndexerPrice);
            }
        }

        return $prices;
    }

    /**
     * @param int $websiteId
     * @param array $productsIds
     */
    private function getCatalogRulePrices(int $websiteId, array $productsIds)
    {
        $connection = $this->getConnection();
        $select = $connection->select();
        $select->join(
            ['cpiw' => $this->catalogPriceResourceModel->getTable('catalog_product_index_website')],
            'cpiw.website_id = i.website_id',
            []
        );
        $select->join(
            ['cpp' => $this->catalogPriceResourceModel->getMainTable()],
            'cpp.website_id = cpiw.website_id'
            . ' AND cpp.rule_date = cpiw.website_date',
            []
        );

        // Only default customer Group ID (0) is supported now
        $customerGroupId = 0;
        $select->where('cpp.product_id IN (?)', $productsIds);
        $select->where('cpp.customer_group_id = ?', $customerGroupId);
        $select->where('cpp.website_id = ?', $websiteId);
        $select->columns([
            'product_id' => 'cpp.product_id',
            'final_price' => 'cpp.rule_price',
        ]);

        return $connection->fetchPairs($select);
    }

    /**
     * @param int $websiteId
     * @param int $customerGroupId
     *
     * @return string
     */
    private function getPriceIndexTableName(int $websiteId, int $customerGroupId): string
    {
        return $this->priceTableResolver->resolve($websiteId, $customerGroupId);
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
