<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Attributes;

/**
 * Class ConfigurableAttributes
 */
class ConfigurableAttributes
{
    /**
     * @var array
     */
    private $requiredAttributes = [
        'sku',
        'status',
        'visibility',
        'name',
        'image',
        'small_image',
        'thumbnail',
        'id',
        'price',
        'special_price',
        'special_to_date',
        'special_from_date',
    ];

    /**
     * @return array
     */
    public function getChildrenRequiredAttributes()
    {
        return $this->requiredAttributes;
    }
}
