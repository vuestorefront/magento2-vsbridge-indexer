<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types=1);

namespace Divante\VsbridgeIndexerCatalog\Model;

use Divante\VsbridgeIndexerCatalog\Api\LoadInventoryInterface;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Inventory as InventoryResource;

/**
 * Class LoadInventory
 */
class LoadInventory implements LoadInventoryInterface
{
    /**
     * @var InventoryResource
     */
    private $resource;

    /**
     * LoadChildrenInventory constructor.
     *
     * @param InvetoryResource $resource
     */
    public function __construct(InventoryResource $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $indexData, int $storeId): array
    {
        $productIds = array_keys($indexData);

        return $this->resource->loadInventory($productIds);
    }
}
