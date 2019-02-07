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

        $galleryPerProduct = $this->galleryProcessor->prepareMediaGallery($gallerySet);

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
}
