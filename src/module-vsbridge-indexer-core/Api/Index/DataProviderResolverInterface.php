<?php

namespace Divante\VsbridgeIndexerCore\Api\Index;

use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;

/**
 * DataProvider resolver. Get dataprovider by entity type.
 *
 * @api
 */
interface DataProviderResolverInterface
{
    /**
     * @param string $type
     * @return DataProviderInterface[]
     */
    public function getDataProviders(string $type);
}
