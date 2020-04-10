<?php
/**
 * Copyright Divante Sp. z o.o.
 * See LICENSE_DIVANTE.txt for license details.
 */
declare(strict_types=1);

namespace Divante\VsbridgeIndexerCatalog\Model\Product;

/**
 * Interface GetParentsByChildIdInterface
 */
interface GetParentsByChildIdInterface
{
    /**
     * Retrieve parent sku array by requested children
     *
     * @param array $childId
     *
     * @return array
     */
    public function execute(array $childId): array;
}
