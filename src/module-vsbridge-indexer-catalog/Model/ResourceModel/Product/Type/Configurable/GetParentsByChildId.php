<?php
/**
 * Copyright Divante Sp. z o.o.
 * See LICENSE_DIVANTE.txt for license details.
 */
declare(strict_types=1);

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Type\Configurable;

use Divante\VsbridgeIndexerCatalog\Model\Product\GetParentsByChildIdInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionProvider;
use Magento\Framework\App\ResourceConnection;

/**
 * Class GetParentsByChildId
 */
class GetParentsByChildId implements GetParentsByChildIdInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var OptionProvider
     */
    private $optionProvider;

    /**
     * GetParentsByChildId constructor.
     *
     * @param OptionProvider $optionProvider
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        OptionProvider $optionProvider,
        ResourceConnection $resourceConnection
    ) {
        $this->optionProvider = $optionProvider;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritDoc
     *
     * @param array $childId
     *
     * @return array
     */
    public function execute(array $childId): array
    {
        $connection = $this->resourceConnection->getConnection();

        $parentSku = [];
        $select = $connection->select()
            ->from(['l' => 'catalog_product_super_link'], ['l.product_id'])
            ->join(
                ['e' => $this->resourceConnection->getTableName('catalog_product_entity')],
                'e.' . $this->optionProvider->getProductEntityLinkField() . ' = l.parent_id',
                ['e.sku']
            )->where('l.product_id IN(?)', $childId);

        foreach ($connection->fetchAll($select) as $row) {
            $parentSku[$row['product_id']] = $parentSku[$row['product_id']] ?? [];
            $parentSku[$row['product_id']][] = $row['sku'];
        }

        return $parentSku;
    }
}
