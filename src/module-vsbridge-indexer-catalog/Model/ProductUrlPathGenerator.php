<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model;

use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Rewrite as RewriteResource;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;

/**
 * Class ProductUrlPathGenerator
 */
class ProductUrlPathGenerator
{
    /**
     * @var RewriteResource
     */
    private $rewriteResource;

    /**
     * @var string
     */
    private $productUrlSuffix;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ProductUrlPathGenerator constructor.
     *
     * @param RewriteResource $rewrite
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        RewriteResource $rewrite,
        ScopeConfigInterface $config
    ) {
        $this->scopeConfig = $config;
        $this->rewriteResource = $rewrite;
    }

    /**
     * @param array $products
     * @param int $storeId
     *
     * @return array
     */
    public function addUrlPath(array $products, $storeId)
    {
        $productIds = array_keys($products);
        $urlSuffix = $this->getProductUrlSuffix();

        $rewrites = $this->rewriteResource->getRawRewritesData($productIds, $storeId);

        foreach ($rewrites as $productId => $rewrite) {
            $rewrite = mb_substr($rewrite, 0, -strlen($urlSuffix));
            $products[$productId]['url_path'] = $rewrite;
        }

        return $products;
    }

    /**
     * Retrieve product rewrite suffix for store
     *
     * @return string
     */
    private function getProductUrlSuffix()
    {
        if (null === $this->productUrlSuffix) {
            $this->productUrlSuffix = $this->scopeConfig->getValue(
                \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator::XML_PATH_PRODUCT_URL_SUFFIX
            );
        }

        return $this->productUrlSuffix;
    }
}
