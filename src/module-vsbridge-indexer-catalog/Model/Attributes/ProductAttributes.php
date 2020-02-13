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
     * @return array
     */
    public function getAttributes(): array
    {
        $attributes = $this->catalogConfig->getAllowedAttributesToIndex();

        if (empty($attributes)) {
            return [];
        }

        return array_merge($attributes, self::REQUIRED_ATTRIBUTES);
    }
}
