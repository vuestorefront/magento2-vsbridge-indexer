<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Marcin Dykas <mdykas@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types=1);

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product;

use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Downloadable as DownloadableResource;

/**
 * Class DownloadableData
 */
class DownloadableData implements DataProviderInterface
{
    const EXTENSION_ATTRIBUTES_FIELD_NAME = 'extension_attributes';

    const DOWNLOADABLE_PRODUCT_LINKS_FIELD_NAME = 'downloadable_product_links';

    const DOWNLOADABLE_PRODUCT_SAMPLES_FIELD_NAME = 'downloadable_product_samples';

    /**
     * @var DownloadableResource
     */
    private $downloadableResource;

    /**
     * DownloadableData constructor.
     * @param DownloadableResource $downloadableResource
     */
    public function __construct(
        DownloadableResource $downloadableResource
    ) {
        $this->downloadableResource = $downloadableResource;
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        foreach ($indexData as $productId => $productData) {
            if ($productData['type_id'] == 'downloadable') {
                $links = $this->downloadableResource->getDownloadableLinksByProductId($productId, $storeId);

                if (!empty($links)) {
                    $indexData[$productId][self::EXTENSION_ATTRIBUTES_FIELD_NAME][self::DOWNLOADABLE_PRODUCT_LINKS_FIELD_NAME] = $links;
                }

                $samples = $this->downloadableResource->getDownloadableSamplesByProductId($productId, $storeId);
                if (!empty($samples)) {
                    $indexData[$productId][self::EXTENSION_ATTRIBUTES_FIELD_NAME][self::DOWNLOADABLE_PRODUCT_SAMPLES_FIELD_NAME] = $samples;
                }
            }
        }

        return $indexData;
    }
}
