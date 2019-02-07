<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product;

use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Prices as Resource;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\TierPrices as TierPricesResource;
use Magento\Customer\Model\Group;
use Magento\Store\Model\StoreManagerInterface;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\AttributeDataProvider;
use Divante\VsbridgeIndexerCatalog\Model\ProductMetaData;

/**
 * Class PriceData
 */
class PriceData implements DataProviderInterface
{
    /**
     * @var \Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\TierPrices
     */
    private $tierPriceResource;

    /**
     * @var AttributeDataProvider
     */
    private $attributeDataProvider;

    /**
     * @var \Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Prices
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
     * PriceData constructor.
     *
     * @param Resource $resource
     * @param TierPricesResource $tierPricesResource
     * @param StoreManagerInterface $storeManager
     * @param ProductMetaData $productMetaData
     * @param AttributeDataProvider $config
     */
    public function __construct(
        Resource $resource,
        TierPricesResource $tierPricesResource,
        StoreManagerInterface $storeManager,
        ProductMetaData $productMetaData,
        AttributeDataProvider $config
    ) {
        $this->resource = $resource;
        $this->tierPriceResource = $tierPricesResource;
        $this->storeManager = $storeManager;
        $this->productMetaData = $productMetaData;
        $this->attributeDataProvider = $config;
    }

    /**
     * @param array $indexData
     * @param int $storeId
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addData(array $indexData, $storeId)
    {
        $productIds = array_keys($indexData);
        $priceData = $this->resource->loadPriceData($storeId, $productIds);

        foreach ($priceData as $priceDataRow) {
            $productId = $priceDataRow['entity_id'];
            $indexData[$productId]['final_price'] = $priceDataRow['final_price'];
            $indexData[$productId]['regular_price'] = $priceDataRow['price'];
        }

        return $this->applyTierGroupPrices($indexData, $storeId);
    }

    /**
     * @param array $indexData
     * @param       $storeId
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function applyTierGroupPrices(array $indexData, $storeId)
    {
        $linkField = $this->productMetaData->get()->getLinkField();
        $linkFieldIds = array_column($indexData, $linkField);
        $websiteId = $this->getWebsiteId($storeId);

        $tierPrices = $this->tierPriceResource->loadTierPrices($websiteId, $linkFieldIds);
        /** @var \Magento\Catalog\Model\Product\Attribute\Backend\TierPrice $backend */
        $backend = $this->getTierPriceAttribute()->getBackend();

        foreach ($indexData as $productId => $product) {
            $linkFieldValue = $product[$linkField];

            if (isset($tierPrices[$linkFieldValue])) {
                $tierRowsData = $tierPrices[$linkFieldValue];
                $tierRowsData = $backend->preparePriceData(
                    $tierRowsData,
                    $indexData[$productId]['type_id'],
                    $websiteId
                );

                foreach ($tierRowsData as $tierRowData) {
                    if (Group::NOT_LOGGED_IN_ID === $tierRowData['cust_group'] && $tierRowData['price_qty'] == 1) {
                        /*vsf does not group prices so we are setting it in */
                        $price = (float) $indexData[$productId]['price'];
                        $price = min((float) $tierRowData['price'], $price);
                        $indexData[$productId]['price'] = $price;
                    }

                    $indexData[$productId]['tier_prices'][] = $this->prepareTierPrices($tierRowData);
                }
            } else {
                $indexData[$productId]['tier_prices'] = [];
            }
        }

        return $indexData;
    }

    /**
     * @param array $productTierPrice
     *
     * @return array
     */
    private function prepareTierPrices(array $productTierPrice)
    {
        return [
            'customer_group_id' => (int) $productTierPrice['cust_group'],
            'value' => (float) $productTierPrice['price'],
            'qty' => (float) $productTierPrice['price_qty'],
            'extension_attributes' => ['website_id' => (int) $productTierPrice['website_id']],
        ];
    }

    /**
     * @param int $storeId
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getWebsiteId($storeId)
    {
        $attribute = $this->getTierPriceAttribute();
        $websiteId = 0;

        if ($attribute->isScopeGlobal()) {
            $websiteId = 0;
        } elseif ($storeId) {
            $websiteId = (int) ($this->storeManager->getStore($storeId)->getWebsiteId());
        }

        return $websiteId;
    }

    /**
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getTierPriceAttribute()
    {
        return $this->attributeDataProvider->getAttributeByCode('tier_price');
    }
}
