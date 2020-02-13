<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Attributes;

use Divante\VsbridgeIndexerCatalog\Api\CatalogConfigurationInterface;

/**
 * Class ConfigurableAttributes
 */
class ConfigurableAttributes
{

    /**
     * This product attributes always be exported for configurable_children
     * @var array
     */
    const MINIMAL_ATTRIBUTE_SET = [
        'sku',
        'status',
        'visibility',
        'name',
        'price',
    ];

    /**
     * @var CatalogConfigurationInterface
     */
    private $catalogConfig;

    /**
     * @var array
     */
    private $requiredAttributes;

    /**
     * @var bool
     */
    private $canIndexMediaGallery;

    /**
     * ConfigurableAttributes constructor.
     *
     * @param CatalogConfigurationInterface $catalogConfiguration
     */
    public function __construct(CatalogConfigurationInterface $catalogConfiguration)
    {
        $this->catalogConfig = $catalogConfiguration;
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function getChildrenRequiredAttributes(int $storeId): array
    {
        if (null === $this->requiredAttributes) {
            $attributes = $this->catalogConfig->getAllowedChildAttributesToIndex($storeId);

            if (empty($attributes)) {
                $this->requiredAttributes = [];
            } else {
                $this->requiredAttributes = array_merge($attributes, self::MINIMAL_ATTRIBUTE_SET);
            }
        }

        return $this->requiredAttributes;
    }

    /**
     * @param int $storeId
     *
     * @return bool
     */
    public function canIndexMediaGallery($storeId): bool
    {
        if (null === $this->canIndexMediaGallery) {
            $attributes = $this->getChildrenRequiredAttributes($storeId);
            $this->canIndexMediaGallery = in_array('media_gallery', $attributes) || empty($attributes);
        }

        return $this->canIndexMediaGallery;
    }
}
