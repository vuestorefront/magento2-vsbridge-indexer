<?php declare(strict_types=1);
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product;

use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeIndexerCatalog\Api\LoadTierPricesInterface;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Prices as Resource;

/**
 * Class PriceData
 */
class PriceData implements DataProviderInterface
{
    /**
     * @var \Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Prices
     */
    private $resourcePriceModel;

    /**
     * @var LoadTierPricesInterface
     */
    private $tierPriceLoader;

    /**
     * PriceData constructor.
     *
     * @param Resource $resource
     * @param LoadTierPricesInterface $loadTierPrices
     */
    public function __construct(
        Resource $resource,
        LoadTierPricesInterface $loadTierPrices
    ) {
        $this->resourcePriceModel = $resource;
        $this->tierPriceLoader = $loadTierPrices;
    }

    /**
     * @param array $indexData
     * @param int $storeId
     *
     * @return array
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addData(array $indexData, $storeId)
    {
        $productIds = array_keys($indexData);
        $priceData = $this->resourcePriceModel->loadPriceData($storeId, $productIds);

        foreach ($priceData as $productId => $priceDataRow) {
            $indexData[$productId]['final_price'] = $this->preparePrice($priceDataRow['final_price']);

            if (isset($priceDataRow['price'])) {
                $indexData[$productId]['regular_price'] = $this->preparePrice($priceDataRow['price']);
            }
        }

        return $this->tierPriceLoader->execute($indexData, $storeId);
    }

    /**
     * @param string $value
     *
     * @return float
     */
    private function preparePrice($value): float
    {
        return (float)$value;
    }
}
