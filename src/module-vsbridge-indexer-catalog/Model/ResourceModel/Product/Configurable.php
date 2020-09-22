<?php

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product;

use Divante\VsbridgeIndexerCatalog\Model\ProductMetaData;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Helper as DbHelper;
use Psr\Log\LoggerInterface;

/**
 * Class Configurable
 */
class Configurable
{

    /**
     * @var DbHelper
     */
    private $dbHelper;

    /**
     * @var AttributeDataProvider
     */
    private $attributeDataProvider;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var Product
     */
    private $productResource;

    /**
     * @var ProductMetaData
     */
    private $productMetaData;

    /**
     * Array of the ids of configurable products from $productCollection
     *
     * @var array
     */
    private $configurableProductIds;

    /**
     * All associated simple products from configurables in $configurableProductIds
     *
     * @var array
     */
    private $simpleProducts;

    /**
     * Array of associated simple product ids.
     * The array index are configurable product ids, the array values are
     * arrays of the associated simple product ids.
     *
     * @var array
     */
    private $associatedSimpleProducts;

    /**
     * Array keys are the configurable product ids,
     * Values: super_product_attribute_id, attribute_id, position
     *
     * @var array
     */
    private $configurableProductAttributes;

    /**
     * @var array
     */
    private $configurableAttributesInfo;

    /**
     * @var array
     */
    private $productsData;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Configurable constructor.
     *
     * @param LoggerInterface $logger
     * @param AttributeDataProvider $attributeDataProvider
     * @param Product $productResource
     * @param ProductMetaData $productMetaData
     * @param ResourceConnection $resourceConnection
     * @param DbHelper $dbHelper
     */
    public function __construct(
        LoggerInterface $logger,
        AttributeDataProvider $attributeDataProvider,
        Product $productResource,
        ProductMetaData $productMetaData,
        ResourceConnection $resourceConnection,
        DbHelper $dbHelper
    ) {
        $this->attributeDataProvider = $attributeDataProvider;
        $this->resource = $resourceConnection;
        $this->productMetaData = $productMetaData;
        $this->productResource = $productResource;
        $this->logger = $logger;
        $this->dbHelper = $dbHelper;
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->productsData = null;
        $this->associatedSimpleProducts = null;
        $this->configurableAttributesInfo = null;
        $this->configurableProductAttributes = null;
        $this->simpleProducts = null;
        $this->configurableProductIds = null;
    }

    /**
     * @param array $products
     *
     * @return void
     */
    public function setProducts(array $products)
    {
        $this->productsData = $products;
    }

    /**
     * Return the attribute values of the associated simple products
     *
     * @param array $product Configurable product.
     *
     * @return array
     * @throws \Exception
     */
    public function getProductConfigurableAttributes(array $product, $storeId)
    {
        if ($product['type_id'] != ConfigurableType::TYPE_CODE) {
            return [];
        }

        $attributeIds = $this->getProductConfigurableAttributeIds($product);

        if (empty($attributeIds)) {
            return [];
        }

        $attributes = $this->getConfigurableAttributeFullInfo($storeId);
        $data = [];

        foreach ($attributeIds as $attributeId) {
            $code = $attributes[$attributeId]['attribute_code'];
            $data[$code] = $this->configurableAttributesInfo[$attributeId];
        }

        return $data;
    }

    /**
     * Return array of configurable attribute ids of the given configurable product.
     *
     * @param array $product
     *
     * @return array
     */
    private function getProductConfigurableAttributeIds(array $product)
    {
        $attributes = $this->getConfigurableProductAttributes();
        $linkField = $this->productMetaData->get()->getLinkField();
        $linkFieldValue = $product[$linkField];

        if (!isset($attributes[$linkFieldValue])) {
            $entityField = $this->productMetaData->get()->getIdentifierField();
            $this->logger->error(
                sprintf('Cannot find super attribute for Product %d [%s]', $linkFieldValue, $entityField)
            );

            return [];
        }

        return explode(',', $attributes[$linkFieldValue]['attribute_ids']);
    }

    /**
     * Load all configurable attributes used in the current product collection.
     *
     * @return array
     */
    private function getConfigurableProductAttributes()
    {
        if (!$this->configurableProductAttributes) {
            $productIds = $this->getParentIds();
            $attributes = $this->getConfigurableAttributesForProductsFromResource($productIds);
            $this->configurableProductAttributes = $attributes;
        }

        return $this->configurableProductAttributes;
    }

    /**
     * This method actually would belong into a resource model, but for easier
     * reference I dropped it into the helper here.
     *
     * @param array $productIds
     *
     * @return array
     */
    private function getConfigurableAttributesForProductsFromResource(array $productIds)
    {
        $select = $this->getConnection()->select()
            ->from(
                $this->resource->getTableName('catalog_product_super_attribute'),
                [
                    'product_id',
                    'product_super_attribute_id',
                ]
            )
            ->group('product_id')
            ->where('product_id IN (?)', $productIds);
        $this->dbHelper->addGroupConcatColumn($select, 'attribute_ids', 'attribute_id');

        return $this->getConnection()->fetchAssoc($select);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getConfigurableAttributeCodes($storeId)
    {
        $attributes = $this->getConfigurableAttributeFullInfo($storeId);

        return array_column($attributes, 'attribute_code');
    }

    /**
     * Return array of all configurable attributes in the current collection.
     * Array indexes are the attribute ids, array values the attribute code
     *
     * @return array
     * @throws \Exception
     */
    private function getConfigurableAttributeFullInfo($storeId)
    {
        if (null === $this->configurableAttributesInfo) {
            // build list of all configurable attribute codes for the current collection
            $this->configurableAttributesInfo = [];

            foreach ($this->getConfigurableProductAttributes() as $configurableAttribute) {
                $attributeIds = explode(',', $configurableAttribute['attribute_ids']);

                foreach ($attributeIds as $attributeId) {
                    if ($attributeId && !isset($this->configurableAttributesInfo[$attributeId])) {
                        $attributeModel = $this->attributeDataProvider->getAttributeById($attributeId);

                        $this->configurableAttributesInfo[$attributeId] = [
                            'attribute_id' => (int)$attributeId,
                            'attribute_code' => $attributeModel->getAttributeCode(),
                            'label' => $attributeModel->getStoreLabel($storeId),
                        ];
                    }
                }
            }
        }

        return $this->configurableAttributesInfo;
    }

    /**
     * Return array of ids of configurable products in the current product collection
     *
     * @return array
     */
    private function getConfigurableProductIds()
    {
        if (null === $this->configurableProductIds) {
            $linkField = $this->productMetaData->get()->getLinkField();
            $entityField = $this->productMetaData->get()->getIdentifierField();

            $this->configurableProductIds = [];
            $products = $this->productsData;

            foreach ($products as $product) {
                if ($product['type_id'] == ConfigurableType::TYPE_CODE) {
                    $entityId = $product[$entityField];
                    $linkId = $product[$linkField];
                    $this->configurableProductIds[$linkId] = $entityId;
                }
            }
        }

        return $this->configurableProductIds;
    }

    /**
     * @return array
     */
    private function getParentIds()
    {
        $productIds = $this->getConfigurableProductIds();

        return array_keys($productIds);
    }

    /**
     * Return all associated simple products for the configurable products in
     * the current product collection.
     *
     * @param int $storeId
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSimpleProducts($storeId)
    {
        if (null === $this->simpleProducts) {
            $parentIds = $this->getParentIds();
            $childrenProducts = $this->productResource->loadChildrenProducts($parentIds, $storeId);

            /** @var array $product */
            foreach ($childrenProducts as $product) {
                $simpleId = $product['entity_id'];
                $parentIds = explode(',', $product['parent_ids']);
                $parentIds = $this->mapLinkFieldToEntityId($parentIds);
                $product['parent_ids'] = $parentIds;
                $this->simpleProducts[$simpleId] = $product;
            }
        }

        return $this->simpleProducts;
    }

    /**
     * @param array $linkIds
     *
     * @return array
     */
    private function mapLinkFieldToEntityId(array $linkIds)
    {
        $productIds = [];

        foreach ($linkIds as $id) {
            $productIds[] = $this->configurableProductIds[$id];
        }

        return $productIds;
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
}
