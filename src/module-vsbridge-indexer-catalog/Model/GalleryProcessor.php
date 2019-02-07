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
     * @param array $gallerySet
     *
     * @return array
     */
    public function prepareMediaGallery(array $gallerySet)
    {
        $galleryPerProduct = [];

        foreach ($gallerySet as $mediaImage) {
            $linkFieldId    = $mediaImage['row_id'];
            $image['typ'] = 'image';
            $image        = [
                'typ' => 'image',
                'image' => $mediaImage['file'],
                'lab' => $this->getValue('label', $mediaImage),
                'pos' => (int)($this->getValue('position', $mediaImage)),
            ];

            $galleryPerProduct[$linkFieldId][] = $image;
        }

        return $galleryPerProduct;
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
