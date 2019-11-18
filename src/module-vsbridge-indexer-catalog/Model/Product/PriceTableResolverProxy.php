<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types = 1);

namespace Divante\VsbridgeIndexerCatalog\Model\Product;

use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;

/**
 * Class PriceTableResolverProxy
 */
class PriceTableResolverProxy
{
    const DEFAULT_PRICE_INDEXER_TABLE = 'catalog_product_index_price';

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * available from Magento 2.6
     * @var \Magento\Framework\Indexer\DimensionFactory
     */
    private $dimensionFactory;

    /**
     * available from Magento 2.6
     * @var \Magento\Catalog\Model\Indexer\Product\Price\PriceTableResolver
     */
    private $priceTableResolver;

    /**
     * @var array
     */
    private $priceIndexTableName = [];

    /**
     * PriceTableResolver constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param int $websiteId
     * @param int $customerGroupId
     *
     * @return string
     */
    public function resolve(int $websiteId, int $customerGroupId): string
    {
        if (class_exists('\Magento\Catalog\Model\Indexer\Product\Price\PriceTableResolver')) {
            $this->createDimensionFactory();
            $this->createPriceTableResolver();
            $key = $websiteId . '_' . $customerGroupId;

            if (!isset($this->priceIndexTableName[$key])) {
                $priceIndexTableName = $this->priceTableResolver->resolve(
                    self::DEFAULT_PRICE_INDEXER_TABLE,
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

        return self::DEFAULT_PRICE_INDEXER_TABLE;
    }

    /**
     * @return DimensionFactory
     */
    private function createDimensionFactory()
    {
        if (null === $this->dimensionFactory) {
            $this->dimensionFactory = $this->create(\Magento\Framework\Indexer\DimensionFactory::class);
        }

        return $this->dimensionFactory;
    }

    /**
     * @return \Magento\Catalog\Model\Indexer\Product\Price\PriceTableResolver
     */
    private function createPriceTableResolver()
    {
        if (null === $this->priceTableResolver) {
            $this->priceTableResolver = $this->create(
                \Magento\Catalog\Model\Indexer\Product\Price\PriceTableResolver::class
            );
        }

        return $this->priceTableResolver;
    }

    /**
     * @param string $class
     *
     * @return mixed
     */
    private function create($class)
    {
        return $this->objectManager->create($class);
    }
}
