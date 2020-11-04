<?php declare(strict_types=1);
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Product;

use Divante\VsbridgeIndexerCatalog\Api\CatalogConfigurationInterface;
use Divante\VsbridgeIndexerCatalog\Api\LoadTierPricesInterface;
use Divante\VsbridgeIndexerCatalog\Model\ProductMetaData;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\TierPrices as TierPricesResource;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\AttributeDataProvider;

use Magento\Customer\Model\Group;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class LoadTierPrices
 */
class LoadTierPrices implements LoadTierPricesInterface
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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductMetaData
     */
    private $productMetaData;

    /**
     * @var CatalogConfigurationInterface
     */
    private $configSettings;

    /**
     * PriceData constructor.
     *
     * @param CatalogConfigurationInterface $configSettings
     * @param TierPricesResource $tierPricesResource
     * @param StoreManagerInterface $storeManager
     * @param ProductMetaData $productMetaData
     * @param AttributeDataProvider $config
     */
    public function __construct(
        CatalogConfigurationInterface $configSettings,
        TierPricesResource $tierPricesResource,
        StoreManagerInterface $storeManager,
        ProductMetaData $productMetaData,
        AttributeDataProvider $config
    ) {
        $this->tierPriceResource = $tierPricesResource;
        $this->storeManager = $storeManager;
        $this->productMetaData = $productMetaData;
        $this->configSettings = $configSettings;
        $this->attributeDataProvider = $config;
    }

    /**
     * @param array $indexData
     * @param int $storeId
     *
     * @return array
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(array $indexData, int $storeId): array
    {
        if (!$this->syncTierPrices()) {
            return $indexData;
        }

        $linkField = $this->productMetaData->get()->getLinkField();
        $linkFieldIds = array_column($indexData, $linkField);
        $websiteId = $this->getWebsiteId($storeId);

        $tierPrices = $this->tierPriceResource->loadTierPrices($websiteId, $linkFieldIds);
        /** @var \Magento\Catalog\Model\Product\Attribute\Backend\TierPrice $backend */
        $backend = $this->getTierPriceAttribute()->getBackend();

        foreach ($indexData as $productId => $product) {
            $linkFieldValue = $product[$linkField];
            $indexData[$productId]['tier_prices'] = [];

            if (isset($tierPrices[$linkFieldValue])) {
                $tierRowsData = $tierPrices[$linkFieldValue];
                $tierRowsData = $backend->preparePriceData(
                    $tierRowsData,
                    $indexData[$productId]['type_id'],
                    $websiteId
                );

                foreach ($tierRowsData as $tierRowData) {
                    if (Group::NOT_LOGGED_IN_ID === $tierRowData['cust_group'] && $tierRowData['price_qty'] == 1) {
                        $price = (float) $indexData[$productId]['price'];
                        $price = min((float) $tierRowData['price'], $price);
                        $indexData[$productId]['price'] = $price;
                    }

                    $indexData[$productId]['tier_prices'][] = $this->prepareTierPrices($tierRowData);
                }
            }
        }

        return $indexData;
    }

    /**
     * @return bool
     */
    private function syncTierPrices(): bool
    {
        return $this->configSettings->syncTierPrices();
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

        return $attribute->isScopeGlobal()
            ? 0
            : $this->storeManager->getStore($storeId)->getWebsiteId();
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
