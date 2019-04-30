<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel;

/**
 * Interface EavAttributesInterface
 */
interface EavAttributesInterface
{

    /**
     * @param int $storeId
     * @param array $entityIds
     * @param array $requiredAttributes
     *
     * @return array
     * @throws \Exception
     */
    public function loadAttributesData($storeId, array $entityIds, array $requiredAttributes = null);
}
