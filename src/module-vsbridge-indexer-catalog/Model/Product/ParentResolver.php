<?php
/**
 * Copyright Divante Sp. z o.o.
 * See LICENSE_DIVANTE.txt for license details.
 */
declare(strict_types=1);

namespace Divante\VsbridgeIndexerCatalog\Model\Product;

use Magento\Framework\Exception\InputException;

/**
 * Class ParentResolver
 */
class ParentResolver
{
    /**
     * @var GetParentsByChildIdInterface[]
     */
    private $parentProviders = [];

    /**
     * @var array
     */
    private $parentSkus = [];

    /**
     * ParentResolver constructor.
     *
     * @param array $handlers
     *
     * @throws InputException
     */
    public function __construct(array $handlers)
    {
        foreach ($handlers as $handler) {
            if (!$handler instanceof GetParentsByChildIdInterface) {
                throw new InputException(
                    __(
                        'Parent handler %1 doesn\'t implement GetParentsByChildIdInterface',
                        get_class($handler)
                    )
                );
            }
        }

        $this->parentProviders = $handlers;
    }

    /**
     * @param array $childIds
     *
     * @return void
     */
    public function load(array $childIds)
    {
        $this->parentSkus = [];

        foreach ($this->parentProviders as $type => $provider) {
            $this->parentSkus[$type] = $provider->execute($childIds);
        }
    }

    /**
     * @param int $childId
     *
     * @return array
     */
    public function resolveParentSku(int $childId): array
    {
        $fullSkuList = [];

        foreach ($this->parentProviders as $type => $provider) {
            $sku = $this->parentSkus[$type][$childId] ?? [];
            $fullSkuList = array_merge($sku, $fullSkuList);
        }

        return $fullSkuList;
    }
}
