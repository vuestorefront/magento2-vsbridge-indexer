<?php
/**
 * @package   magento-2-1.dev
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product;

use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\AttributeDataProvider;
use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeIndexerCore\Indexer\DataFilter;
use Divante\VsbridgeIndexerCatalog\Model\ConfigSettings;
use Divante\VsbridgeIndexerCatalog\Model\SlugGenerator;
use Divante\VsbridgeIndexerCatalog\Model\ProductUrlPathGenerator;

/**
 * Class AttributeData
 */
class AttributeData implements DataProviderInterface
{
    /**
     * @var AttributeDataProvider
     */
    private $resourceModel;

    /**
     * @var DataFilter
     */
    private $dataFilter;

    /**
     * @var ConfigSettings
     */
    private $settings;

    /**
     * @var SlugGenerator
     */
    private $slugGenerator;

    /**
     * @var AttributeDataProvider
     */
    private $productUrlPathGenerator;

    /**
     * AttributeData constructor.
     *
     * @param ConfigSettings $configSettings
     * @param SlugGenerator\Proxy $slugGenerator
     * @param ProductUrlPathGenerator $productUrlPathGenerator
     * @param DataFilter $dataFilter
     * @param AttributeDataProvider $resourceModel
     */
    public function __construct(
        ConfigSettings $configSettings,
        SlugGenerator\Proxy $slugGenerator,
        ProductUrlPathGenerator $productUrlPathGenerator,
        DataFilter $dataFilter,
        AttributeDataProvider $resourceModel
    ) {
        $this->slugGenerator = $slugGenerator;
        $this->settings = $configSettings;
        $this->resourceModel = $resourceModel;
        $this->dataFilter = $dataFilter;
        $this->productUrlPathGenerator = $productUrlPathGenerator;
    }

    /**
     * @param array $indexData
     * @param int   $storeId
     *
     * @return array
     * @throws \Exception
     */
    public function addData(array $indexData, $storeId)
    {
        $attributes = $this->resourceModel->loadAttributesData($storeId, array_keys($indexData));

        foreach ($attributes as $entityId => $attributesData) {
            $productData = array_merge($indexData[$entityId], $attributesData);

            if ($this->settings->useMagentoUrlKeys()) {
                $productData['slug'] = $productData['url_key'];
            } else {
                $slug = $this->slugGenerator->generate($productData['name'], $entityId);
                $productData['slug'] = $slug;
            }

            $indexData[$entityId] = $productData;
        }

        $attributes = null;
        $indexData = $this->productUrlPathGenerator->addUrlPath($indexData, $storeId);

        return $indexData;
    }
}
