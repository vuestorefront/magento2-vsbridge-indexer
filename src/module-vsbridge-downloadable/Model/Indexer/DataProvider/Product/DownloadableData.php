<?php
/**
 * Copyright Divante Sp. z o.o.
 * See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types=1);

namespace Divante\VsbridgeDownloadable\Model\Indexer\DataProvider\Product;

use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeDownloadable\Model\ResourceModel\Product\Downloadable as DownloadableResource;

/**
 * Class DownloadableData
 */
class DownloadableData implements DataProviderInterface
{
    /**
     * @const string
     */
    const EXTENSION_ATTRIBUTES = 'extension_attributes';

    /**
     * @const string
     */
    const LINKS_FIELD_NAME = 'downloadable_product_links';

    /**
     * @const string
     */
    const SAMPLES_FIELD_NAME = 'downloadable_product_samples';

    /**
     * @var DownloadableResource
     */
    private $downloadableResource;

    /**
     * DownloadableData constructor.
     *
     * @param DownloadableResource $downloadableResource
     */
    public function __construct(DownloadableResource $downloadableResource)
    {
        $this->downloadableResource = $downloadableResource;
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $this->downloadableResource->setProducts($indexData);

        $groupedLinks = $this->downloadableResource->getDownloadableLinksByProductId($storeId);
        $groupedSamples = $this->downloadableResource->getDownloadableSamplesByProductId($storeId);

        foreach ($groupedLinks as $productId => $productLinks) {
            $indexData[$productId][self::EXTENSION_ATTRIBUTES][self::LINKS_FIELD_NAME] = $productLinks;
        }

        foreach ($groupedSamples as $productId => $productLinks) {
            $indexData[$productId][self::EXTENSION_ATTRIBUTES][self::SAMPLES_FIELD_NAME] = $productLinks;
        }

        return $indexData;
    }
}
