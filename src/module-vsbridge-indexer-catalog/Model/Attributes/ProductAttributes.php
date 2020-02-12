<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Attributes;

use Divante\VsbridgeIndexerCatalog\Api\Data\CatalogConfigurationInterface;

/**
 * Class ProductAttributes
 */
class ProductAttributes
{
    /**
     * @var array
     */
    const REQUIRED_ATTRIBUTES = [
        'sku',
        'url_path',
        'url_key',
        'name',
        'price',
        'visibility',
        'status',
        'price_type',
    ];

    /**
     * @var CatalogConfigurationInterface
     */
    private $catalogConfig;

    /**
     * ProductAttributes constructor.
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
    public function getAttributes($storeId): array
    {
        $attributes = $this->catalogConfig->getAllowedAttributesToIndex($storeId);

        if (empty($attributes)) {
            return [];
        }

        return array_merge($attributes, self::REQUIRED_ATTRIBUTES);
    }
}
