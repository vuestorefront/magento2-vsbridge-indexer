<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Attributes;

/**
 * Class CategoryChildrenAttributes
 */
class CategoryChildAttributes
{
    /**
     * @var array
     */
    private $requiredAttributes = [
        'name',
        'is_active',
        'url_path',
        'url_key',
    ];

    /**
     * Static category fields
     * @var array
     */
    private $staticAttributes = [
        'id',
        'slug',
        'parent_id',
        'path',
        'position',
        'level',
    ];

    /**
     * @return array
     */
    public function getRequiredAttributes()
    {
        return $this->requiredAttributes;
    }

    /**
     * @return array
     */
    public function getRequiredFields()
    {
        return array_merge($this->requiredAttributes, $this->staticAttributes);
    }
}
