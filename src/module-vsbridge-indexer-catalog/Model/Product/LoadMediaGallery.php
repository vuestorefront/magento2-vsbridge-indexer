<?php declare(strict_types=1);

/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Product;

use Divante\VsbridgeIndexerCatalog\Api\LoadMediaGalleryInterface;
use Divante\VsbridgeIndexerCatalog\Model\ProductMetaData;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Gallery as Resource;

/**
 * Class LoadMediaGallery
 */
class LoadMediaGallery implements LoadMediaGalleryInterface
{
    const VIDEO_TYPE = 'external-video';

    /**
     * Youtube regex
     * @var string
     */
    private $youtubeRegex =
        '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i';

    /**
     * Vimeo regex
     * @var array
     */
    private $vimeoRegex = [
        '%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)',
        "?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im",
    ];

    /**
     * @var \Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Gallery
     */
    private $resourceModel;

    /**
     * @var ProductMetaData
     */
    private $productMetaData;

    /**
     * @var array
     */
    private $rowIdToEntityId = [];

    /**
     * MediaGalleryData constructor.
     *
     * @param Resource $resource
     * @param ProductMetaData $productMetaData
     */
    public function __construct(
        Resource $resource,
        ProductMetaData $productMetaData
    ) {
        $this->resourceModel = $resource;
        $this->productMetaData = $productMetaData;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $indexData, int $storeId): array
    {
        $this->mapRowIdToEntityId($indexData);
        $linkField = $this->productMetaData->get()->getLinkField();
        $linkFieldIds = array_column($indexData, $linkField);

        $gallerySet = $this->resourceModel->loadGallerySet($linkFieldIds, $storeId);
        $valueIds = $this->getValueIds($gallerySet);
        $videoSet = $this->resourceModel->loadVideos($valueIds, $storeId);

        foreach ($gallerySet as $mediaImage) {
            $linkFieldId  = $mediaImage['row_id'];
            $entityId = $this->rowIdToEntityId[$linkFieldId] ?? $linkFieldId;

            $image['typ'] = 'image';
            $image        = [
                'typ' => 'image',
                'image' => $mediaImage['file'],
                'lab' => $this->getValue('label', $mediaImage),
                'pos' => (int)($this->getValue('position', $mediaImage)),
            ];

            $valueId = $mediaImage['value_id'];

            if (isset($videoSet[$valueId]) && isset($videoSet[$valueId]['url']) && is_string($videoSet[$valueId]['url'])) {
                if($videoSet[$valueId]['disabled'] === "0"){
                    $image['vid'] = $this->prepareVideoData($videoSet[$valueId]);
                    $indexData[$entityId]['media_gallery'][] = $image;
                }
            } else{
                $indexData[$entityId]['media_gallery'][] = $image;
            }
        }

        $this->rowIdToEntityId = [];

        return $indexData;
    }

    /**
     * Map Row Id to Entity Id
     *
     * @param array $products
     *
     * @return void
     */
    private function mapRowIdToEntityId(array $products)
    {
        $linkField = $this->productMetaData->get()->getLinkField();
        $identifierField = $this->productMetaData->get()->getIdentifierField();

        if ($identifierField !== $linkField) {
            foreach ($products as $entityId => $product) {
                $this->rowIdToEntityId[$product[$linkField]] = $entityId;
            }
        }
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

    /**
     * @param array $video
     *
     * @return array
     */
    private function prepareVideoData(array $video)
    {
        $vimeoRegex = implode('', $this->vimeoRegex);
        $id = null;
        $type = null;
        $reg = [];
        $url = $video['url'];

        if (preg_match($this->youtubeRegex, $url, $reg)) {
            $id = $reg[1];
            $type = 'youtube';
        } elseif (preg_match($vimeoRegex, $video['url'], $reg)) {
            $id = $reg[3];
            $type = 'vimeo';
        }

        $video['video_id'] = $id;
        $video['type'] = $type;

        return $video;
    }

    /**
     * @param string $fieldKey
     * @param array  $image
     *
     * @return string
     */
    private function getValue($fieldKey, array $image)
    {
        if (isset($image[$fieldKey]) && (null !== $image[$fieldKey])) {
            return $image[$fieldKey];
        }

        if (isset($image[$fieldKey . '_default'])) {
            return $image[$fieldKey . '_default'];
        }

        return '';
    }
}
