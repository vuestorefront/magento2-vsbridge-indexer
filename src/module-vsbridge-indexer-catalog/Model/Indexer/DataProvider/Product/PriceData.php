<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product;

use Divante\VsbridgeIndexerCatalog\Model\TierPriceProcessor;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Prices as Resource;
use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;

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
     * @var TierPriceProcessor
     */
    private $tierPriceProcessor;

    /**
     * PriceData constructor.
     *
     * @param Resource $resource
     * @param TierPriceProcessor $tierPriceProcessor
     */
    public function __construct(
        Resource $resource,
        TierPriceProcessor $tierPriceProcessor
    ) {
        $this->resourcePriceModel = $resource;
        $this->tierPriceProcessor = $tierPriceProcessor;
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
            $indexData[$productId]['regular_price'] = $this->preparePrice($priceDataRow['price']);
        }

        return $this->tierPriceProcessor->applyTierGroupPrices($indexData, $storeId);
    }

    /**
     * @param string $value
     *
     * @return float
     */
    private function preparePrice($value)
    {
        return (float)$value;
    }
}
