<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Marcin Dykas <mdykas@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types=1);

namespace Divante\VsbridgeIndexerCatalog\Model\Product;

use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product;

/**
 * Class ParentResolver
 */
class ParentResolver
{
    /**
     * @var Product
     */
    private $productResource;

    /**
     * ParentResolver constructor.
     *
     * @param Product $productResource
     */
    public function __construct(
        Product $productResource
    ) {
        $this->productResource = $productResource;
    }

    /**
     * @param array $parentIds
     * @return array
     */
    public function getParentProductsByIds(array $parentIds)
    {
        return $this->productResource->getSkusByIds($parentIds);
    }
}
