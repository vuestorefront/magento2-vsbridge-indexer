<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product;

use Divante\VsbridgeIndexerCatalog\Model\Product\LinkTypeMapper;
use Divante\VsbridgeIndexerCatalog\Model\ProductMetaData;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * Class Links
 */
class Links
{

    /**
     * @var array
     */
    private $products = [];

    /**
     * @var array
     */
    private $links;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var LinkTypeMapper
     */
    private $linkTypeMapper;

    /**
     * @var array
     */
    private $positionAttribute;

    /**
     * @var ProductMetaData
     */
    private $productMetaData;

    /**
     * Links constructor.
     *
     * @param ProductMetaData $productMetaData
     * @param LinkTypeMapper $linkTypeMapper
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ProductMetaData $productMetaData,
        LinkTypeMapper $linkTypeMapper,
        ResourceConnection $resourceConnection
    ) {
        $this->linkTypeMapper = $linkTypeMapper;
        $this->resource = $resourceConnection;
        $this->productMetaData = $productMetaData;
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->links = null;
        $this->products = null;
    }

    /**
     * @param array $products
     *
     * @return void
     */
    public function setProducts(array $products)
    {
        $this->products = $products;
    }

    /**
     * @param array $product
     *
     * @return array
     */
    public function getLinkedProduct(array $product)
    {
        $links = $this->getAllLinkedProducts();
        $productId = $product['id'];

        if (isset($links[$productId])) {
            $linkProductList = [];

            foreach ($links[$productId] as $linkData) {
                $typeId = (int)$linkData['link_type_id'];
                $linkType = $this->getLinkType($typeId);

                if ($linkType) {
                    $position = isset($linkData['position']) ? (int)$linkData['position'] : 0;
                    $linkProductList[] = [
                        'sku' => $product['sku'],
                        'link_type' => $linkType,
                        'linked_product_sku' => $linkData['sku'],
                        'linked_product_type' => $linkData['type_id'],
                        'position' => $position,
                    ];
                }
            }

            return $linkProductList;
        }

        return [];
    }

    /**
     * @param int $typeId
     *
     * @return string|null
     */
    private function getLinkType($typeId)
    {
        return $this->linkTypeMapper->map($typeId);
    }

    /**
     * @return array
     */
    private function getAllLinkedProducts()
    {
        if (null === $this->links) {
            $select = $this->prepareLinksSelect();
            $links = $this->getConnection()->fetchAll($select);
            $groupByProduct = [];

            foreach ($links as $link) {
                $productId = $link['product_id'];
                unset($link['product_id']);
                $groupByProduct[$productId][] = $link;
            }

            $this->links = $groupByProduct;
        }

        return $this->links;
    }

    /**
     * @return Select
     */
    private function prepareLinksSelect()
    {
        $productIds = $this->getProductsIds();

        $select = $this->getConnection()->select()
            ->from(
                ['links' => $this->resource->getTableName('catalog_product_link')],
                [
                    'product_id',
                    'linked_product_id',
                    'link_type_id',
                ]
            )
            ->where('product_id in (?)', $productIds);

        $select->joinLeft(
            ['entity' => $this->resource->getTableName('catalog_product_entity')],
            'links.linked_product_id = entity.entity_id',
            [
                'sku',
                'type_id',
            ]
        );

        return $this->joinPositionAttribute($select);
    }

    /**
     * @param Select $select
     *
     * @return Select
     */
    private function joinPositionAttribute(Select $select)
    {
        $alias = 'link_position';
        $attributePosition = $this->fetchPositionAttributeData();

        if (empty($attributePosition)) {
            return $select;
        }

        $table = $this->resource->getTableName($this->getAttributeTypeTable($attributePosition['type']));

        $joinCondition = [
            "{$alias}.link_id = links.link_id",
            $this->getConnection()->quoteInto(
                "{$alias}.product_link_attribute_id = ?",
                $attributePosition['id']
            ),
        ];

        $select->joinLeft(
            [$alias => $table],
            implode(' AND ', $joinCondition),
            [$attributePosition['code'] => 'value']
        );

        return $select;
    }

    /**
     * @return array
     */
    private function fetchPositionAttributeData()
    {
        if (null === $this->positionAttribute) {
            $select = $this->getConnection()->select()
                ->from(
                    $this->resource->getTableName('catalog_product_link_attribute'),
                    [
                        'id' => 'product_link_attribute_id',
                        'code' => 'product_link_attribute_code',
                        'type' => 'data_type',
                    ]
                )
                ->where('product_link_attribute_code = ?', 'position');

            $this->positionAttribute = $this->getConnection()->fetchRow($select);
        }

        return $this->positionAttribute;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function getAttributeTypeTable($type)
    {
        return $this->resource->getTableName('catalog_product_link_attribute_' . $type);
    }

    /**
     * Add product filter to collection
     *
     * @return int[]
     */
    private function getProductsIds()
    {
        $products = $this->getProducts();
        $linkField = $this->productMetaData->get()->getLinkField();

        return array_column($products, $linkField);
    }

    /**
     * @return array
     */
    private function getProducts()
    {
        return $this->products;
    }
}
