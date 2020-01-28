<?php declare(strict_types = 1);

/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product\Configurable;

use Divante\VsbridgeIndexerCatalog\Model\Attributes\ConfigurableAttributes;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\AttributeDataProvider;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Prices as PriceResourceModel;
use Divante\VsbridgeIndexerCatalog\Api\LoadTierPricesInterface;
use Divante\VsbridgeIndexerCatalog\Api\Data\CatalogConfigurationInterface;
use Divante\VsbridgeIndexerCatalog\Api\LoadMediaGalleryInterface;

/**
 * Class LoadChildrenRawAttributes
 */
class LoadChildrenRawAttributes
{
    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var LoadTierPricesInterface
     */
    private $loadTierPrices;

    /**
     * @var PriceResourceModel
     */
    private $priceResourceModel;

    /**
     * @var  AttributeDataProvider
     */
    private $resourceAttributeModel;

    /**
     * @var ConfigurableAttributes
     */
    private $configurableAttributes;

    /**
     * @var LoadMediaGalleryInterface
     */
    private $mediaGalleryLoader;

    /**
     * @var CatalogConfigurationInterface
     */
    private $settings;

    /**
     * LoadChildrenRawAttributes constructor.
     *
     * @param CatalogConfigurationInterface $catalogConfiguration
     * @param AttributeDataProvider $attributeDataProvider
     * @param ConfigurableAttributes $configurableAttributes
     * @param LoadTierPricesInterface $loadTierPrices
     * @param LoadMediaGalleryInterface $loadMediaGallery
     * @param PriceResourceModel $priceResourceModel
     * @param int $batchSize
     */
    public function __construct(
        CatalogConfigurationInterface $catalogConfiguration,
        AttributeDataProvider $attributeDataProvider,
        ConfigurableAttributes $configurableAttributes,
        LoadTierPricesInterface $loadTierPrices,
        LoadMediaGalleryInterface $loadMediaGallery,
        PriceResourceModel $priceResourceModel,
        $batchSize = 500
    ) {
        $this->batchSize = $batchSize;
        $this->settings = $catalogConfiguration;
        $this->loadTierPrices = $loadTierPrices;
        $this->mediaGalleryLoader = $loadMediaGallery;
        $this->priceResourceModel = $priceResourceModel;
        $this->resourceAttributeModel = $attributeDataProvider;
        $this->configurableAttributes = $configurableAttributes;
    }

    /**
     * @param int $storeId
     * @param array $allChildren
     * @param array $configurableAttributeCodes
     *
     * @return array
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($storeId, array $allChildren, array $configurableAttributeCodes)
    {
        $requiredAttributes = $this->getRequiredChildrenAttributes();

        if (!empty($requiredAttributes)) {
            $requiredAttributes = array_merge(
                $requiredAttributes,
                $configurableAttributeCodes
            );
        }

        $requiredAttribute = array_unique($requiredAttributes);

        foreach ($this->getChildrenInBatches($allChildren, $this->batchSize) as $batch) {
            $childIds = array_keys($batch);
            $priceData = $this->priceResourceModel->loadPriceData($storeId, $childIds);

            $allAttributesData = $this->resourceAttributeModel->loadAttributesData(
                $storeId,
                $childIds,
                $requiredAttribute
            );

            foreach ($priceData as $childId => $priceDataRow) {
                $allChildren[$childId]['final_price'] = (float)$priceDataRow['final_price'];

                if (isset($priceDataRow['price'])) {
                    $allChildren[$childId]['regular_price'] = (float)$priceDataRow['price'];
                }
            }

            foreach ($allAttributesData as $productId => $attributes) {
                $newProductData = array_merge(
                    $allChildren[$productId],
                    $attributes
                );

                if (
                    $this->settings->syncTierPrices() ||
                    $this->configurableAttributes->canIndexMediaGallery()
                ) {
                    /*we need some extra attributes to apply tier prices*/
                    $batch[$productId] = $newProductData;
                } else {
                    $allChildren[$productId] = $newProductData;
                }
            }

            $replace = false;

            if ($this->settings->syncTierPrices()) {
                $batch = $this->loadTierPrices->execute($batch, $storeId);
                $replace = true;
            }

            if ($this->configurableAttributes->canIndexMediaGallery()) {
                $batch = $this->mediaGalleryLoader->execute($batch, $storeId);
                $replace = true;
            }

            if ($replace) {
                $allChildren = array_replace_recursive($allChildren, $batch);
            }
        }

        return $allChildren;
    }

    /**
     * @return array
     */
    private function getRequiredChildrenAttributes(): array
    {
        return $this->configurableAttributes->getChildrenRequiredAttributes();
    }

    /**
     * @param array $documents
     * @param int $batchSize
     *
     * @return \Generator
     */
    private function getChildrenInBatches(array $documents, $batchSize)
    {
        $i = 0;
        $batch = [];

        foreach ($documents as $documentName => $documentValue) {
            $batch[$documentName] = $documentValue;

            if (++$i == $batchSize) {
                yield $batch;
                $i = 0;
                $batch = [];
            }
        }

        if (count($batch) > 0) {
            yield $batch;
        }
    }
}
