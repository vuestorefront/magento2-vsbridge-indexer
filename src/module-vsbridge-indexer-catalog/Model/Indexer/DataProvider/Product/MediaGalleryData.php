<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product;

use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeIndexerCatalog\Api\LoadMediaGalleryInterface;
use Divante\VsbridgeIndexerCatalog\Api\Data\CatalogConfigurationInterface;

/**
 * Class MediaGalleryData
 */
class MediaGalleryData implements DataProviderInterface
{

    /**
     * @var CatalogConfigurationInterface
     */
    private $catalogConfig;

    /**
     * @var LoadMediaGalleryInterface
     */
    private $galleryConverter;

    /**
     * @var boolean
     */
    private $canIndexMediaGallery;

    /**
     * MediaGalleryData constructor.
     *
     * @param LoadMediaGalleryInterface $galleryProcessor
     */
    public function __construct(
        CatalogConfigurationInterface $catalogConfig,
        LoadMediaGalleryInterface $galleryProcessor
    ) {
        $this->catalogConfig = $catalogConfig;
        $this->galleryConverter = $galleryProcessor;
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        if ($this->canIndexMediaGallery()) {
            return $this->galleryConverter->execute($indexData, $storeId);
        }

        return $indexData;
    }

    /**
     * @return bool
     */
    private function canIndexMediaGallery()
    {
        if (null === $this->canIndexMediaGallery) {
            $attributes = $this->catalogConfig->getAllowedAttributesToIndex();
            $this->canIndexMediaGallery = in_array('media_gallery', $attributes);
        }

        return $this->canIndexMediaGallery;
    }
}
