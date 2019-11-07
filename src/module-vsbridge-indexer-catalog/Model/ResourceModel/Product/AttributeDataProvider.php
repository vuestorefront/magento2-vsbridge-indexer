<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product;

use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\AbstractEavAttributes;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Products Attribute provider
 * Class AttributeDataProvider
 */
class AttributeDataProvider extends AbstractEavAttributes
{

    /**
     * @var AttributeCollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * Product attributes by id
     *
     * @var array
     */
    private $attributesById;

    /**
     * Mapping attribute code to id
     * @var array
     */
    private $attributeCodeToId = [];

    /**
     * AttributeDataProvider constructor.
     *
     * @param Json $serializer
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     * @param string $entityType
     */
    public function __construct(
        Json $serializer,
        AttributeCollectionFactory $attributeCollectionFactory,
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool,
        $entityType = \Magento\Catalog\Api\Data\ProductInterface::class
    ) {
        $this->serializer = $serializer;
        $this->attributeCollectionFactory = $attributeCollectionFactory;

        parent::__construct($resourceConnection, $metadataPool, $entityType);
    }

    /**
     * @return Attribute[]
     */
    public function getAttributesById()
    {
        return $this->initAttributes();
    }

    /**
     * @param int $attributeId
     *
     * @return Attribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeById($attributeId)
    {
        $this->initAttributes();

        if (isset($this->attributesById[$attributeId])) {
            return $this->attributesById[$attributeId];
        }

        throw new \Magento\Framework\Exception\LocalizedException(__('Attribute not found.'));
    }

    /**
     * @param string $attributeCode
     *
     * @return Attribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeByCode($attributeCode)
    {
        $this->initAttributes();

        if (isset($this->attributeCodeToId[$attributeCode])) {
            $attributeId = $this->attributeCodeToId[$attributeCode];

            return $this->attributesById[$attributeId];
        }

        throw new \Magento\Framework\Exception\LocalizedException(__('Attribute not found.'));
    }

    /**
     * @return Attribute[]
     */
    public function initAttributes()
    {
        if (null === $this->attributesById) {
            $attributeCollection = $this->getAttributeCollection();

            foreach ($attributeCollection as $attribute) {
                $this->prepareAttribute($attribute);

                $this->attributesById[$attribute->getId()] = $attribute;
                $this->attributeCodeToId[$attribute->getAttributeCode()] = $attribute->getId();
            }
        }

        return $this->attributesById;
    }

    /**
     * @param Attribute $attribute
     *
     * @return Attribute
     */
    public function prepareAttribute(Attribute $attribute)
    {
        $additionalData = (string)$attribute->getData('additional_data');

        if (!empty($additionalData)) {
            $additionalData = $this->serializer->unserialize($additionalData);

            if (isset($additionalData['swatch_input_type'])) {
                $attribute->setData('swatch_input_type', $additionalData['swatch_input_type']);
            }
        }

        return $attribute;
    }

    /**
     * @return Collection
     */
    public function getAttributeCollection()
    {
        return $this->attributeCollectionFactory->create();
    }
}
