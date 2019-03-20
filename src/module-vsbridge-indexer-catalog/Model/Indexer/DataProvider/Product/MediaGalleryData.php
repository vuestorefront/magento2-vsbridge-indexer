<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product;

use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Gallery as Resource;
use Divante\VsbridgeIndexerCatalog\Model\GalleryProcessor;
use Divante\VsbridgeIndexerCatalog\Model\ProductMetaData;

/**
 * Class MediaGalleryData
 */
class MediaGalleryData implements DataProviderInterface
{
    const VIDEO_TYPE = 'external-video';

    /**
     * @var \Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Gallery
     */
    private $resourceModel;

    /**
     * @var GalleryProcessor
     */
    private $galleryProcessor;

    /**
     * @var ProductMetaData
     */
    private $productMetaData;

    /**
     * MediaGalleryData constructor.
     *
     * @param Resource $resource
     * @param ProductMetaData $productMetaData
     * @param GalleryProcessor $galleryProcessor
     */
    public function __construct(
        Resource $resource,
        ProductMetaData $productMetaData,
        GalleryProcessor $galleryProcessor
    ) {
        $this->resourceModel = $resource;
        $this->productMetaData = $productMetaData;
        $this->galleryProcessor = $galleryProcessor;
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $linkField = $this->productMetaData->get()->getLinkField();
        $linkFieldIds = array_column($indexData, $linkField);

        $gallerySet = $this->resourceModel->loadGallerySet($linkFieldIds, $storeId);
        $valueIds = $this->getValueIds($gallerySet);

        $galleryVideos = $this->resourceModel->loadVideos($valueIds, $storeId);
        $galleryPerProduct = $this->galleryProcessor->prepareMediaGallery($gallerySet, $galleryVideos);

        foreach ($indexData as $productId => $productData) {
            $linkFieldValue = $productData[$linkField];

            if (isset($galleryPerProduct[$linkFieldValue])) {
                $indexData[$productId]['media_gallery'] = $galleryPerProduct[$linkFieldValue];
            } else {
                $indexData[$productId]['media_gallery'] = [];
            }
        }

        return $indexData;
    }

    /**
     * @param array $mediaGallery
     *
     * @return array
     */
    private function getValueIds(array $mediaGallery)
    {
        $valueIds = [];

        foreach ($mediaGallery as $mediaItem) {
            if (self::VIDEO_TYPE === $mediaItem['media_type']) {
                $valueIds[] = $mediaItem['value_id'];
            }
        }

        return $valueIds;
    }
}
