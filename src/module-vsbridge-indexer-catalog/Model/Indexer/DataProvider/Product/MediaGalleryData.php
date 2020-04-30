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
use Divante\VsbridgeIndexerCatalog\Api\CatalogConfigurationInterface;

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
    private $loadMediaGallery;

    /**
     * @var boolean
     */
    private $canIndexMediaGallery;

    /**
     * MediaGalleryData constructor.
     *
     * @param CatalogConfigurationInterface $catalogConfig
     * @param LoadMediaGalleryInterface $galleryProcessor
     */
    public function __construct(
        CatalogConfigurationInterface $catalogConfig,
        LoadMediaGalleryInterface $galleryProcessor
    ) {
        $this->catalogConfig = $catalogConfig;
        $this->loadMediaGallery = $galleryProcessor;
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        if ($this->canIndexMediaGallery($storeId)) {
            return $this->loadMediaGallery->execute($indexData, $storeId);
        }

        return $indexData;
    }

    /**
     * @param int $storeId
     *
     * @return bool
     */
    private function canIndexMediaGallery(int $storeId)
    {
        if (null === $this->canIndexMediaGallery) {
            $attributes = $this->catalogConfig->getAllowedAttributesToIndex($storeId);
            $this->canIndexMediaGallery = empty($attributes) || in_array('media_gallery', $attributes);
        }

        return $this->canIndexMediaGallery;
    }
}
