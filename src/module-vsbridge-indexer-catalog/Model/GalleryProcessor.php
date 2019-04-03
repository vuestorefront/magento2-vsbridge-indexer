<?php
/**
 * @package   magento-2-1.dev
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model;

/**
 * Class GalleryProcessor
 */
class GalleryProcessor
{
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
     * @param array $gallerySet
     * @param array $videoSet
     *
     * @return array
     */
    public function prepareMediaGallery(array $gallerySet, array $videoSet = [])
    {
        $galleryPerProduct = [];

        foreach ($gallerySet as $mediaImage) {
            $linkFieldId  = $mediaImage['row_id'];
            $image['typ'] = 'image';
            $image        = [
                'typ' => 'image',
                'image' => $mediaImage['file'],
                'lab' => $this->getValue('label', $mediaImage),
                'pos' => (int)($this->getValue('position', $mediaImage)),
            ];

            $valueId = $mediaImage['value_id'];

            if (isset($videoSet[$valueId])) {
                $image['vid'] = $this->prepareVideoData($videoSet[$valueId]);
            }

            $galleryPerProduct[$linkFieldId][] = $image;
        }

        return $galleryPerProduct;
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
