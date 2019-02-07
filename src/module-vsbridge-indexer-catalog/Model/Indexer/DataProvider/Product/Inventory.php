<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product;

use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Inventory as Resource;
use Magento\Store\Model\StoreManagerInterface;
use Divante\VsbridgeIndexerCore\Index\Mapping\GeneralMapping;

/**
 * Class Inventory
 */
class Inventory implements DataProviderInterface
{

    /**
     * @var \Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Inventory
     */
    private $resourceModel;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var GeneralMapping
     */
    private $generalMapping;

    /**
     * Inventory constructor.
     *
     * @param Resource $resource
     * @param GeneralMapping $generalMapping
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Resource $resource,
        GeneralMapping $generalMapping,
        StoreManagerInterface $storeManager
    ) {
        $this->resourceModel = $resource;
        $this->storeManager = $storeManager;
        $this->generalMapping = $generalMapping;
    }

    /**
     * @param array $indexData
     * @param int   $storeId
     *
     * @return array
     */
    public function addData(array $indexData, $storeId)
    {
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        $inventoryData = $this->resourceModel->loadInventoryData($websiteId, array_keys($indexData));

        foreach ($inventoryData as $inventoryDataRow) {
            $productId = (int) $inventoryDataRow['product_id'];
            $indexData[$productId]['stock'] = $this->generalMapping->prepareStockData($inventoryDataRow);
        }

        $inventoryData = null;

        return $indexData;
    }
}
