<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Api\DataProvider\Category;

use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;

/**
 * Interface AttributeDataProviderInterface
 */
interface AttributeDataProviderInterface extends DataProviderInterface
{
    /**
     * @param array $categoryDTO
     *
     * @return array
     */
    public function prepareParentCategory(array $categoryDTO);

    /**
     * @param array $categoryDTO
     *
     * @return array
     */
    public function prepareChildCategory(array $categoryDTO);
}
