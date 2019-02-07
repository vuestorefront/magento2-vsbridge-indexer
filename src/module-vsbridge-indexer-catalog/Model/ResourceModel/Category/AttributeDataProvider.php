<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category;

use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\AbstractEavAttributes;
use Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Category\Attribute\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Category Attributes Provider
 *
 * Class AttributeDataProvider
 */
class AttributeDataProvider extends AbstractEavAttributes
{

    /**
     * @var CollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * Category attributes by ID
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
     * @param CollectionFactory $attributeCollectionFactory
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     * @param string $entityType
     */
    public function __construct(
        CollectionFactory $attributeCollectionFactory,
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool,
        $entityType = \Magento\Catalog\Api\Data\CategoryInterface::class
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;

        parent::__construct($resourceConnection, $metadataPool, $entityType);
    }

    /**
     * @param string $attributeCode
     *
     * @return Attribute
     */
    public function getAttributeByCode($attributeCode)
    {
        $this->initAttributes();
        $attributeId = $this->attributeCodeToId[$attributeCode];

        return $this->attributesById[$attributeId];
    }

    /**
     * @return Attribute[]
     */
    public function initAttributes()
    {
        if (null === $this->attributesById) {
            $attributeCollection = $this->getAttributeCollection();

            foreach ($attributeCollection as $attribute) {
                $this->attributesById[$attribute->getId()] = $attribute;
                $this->attributeCodeToId[$attribute->getAttributeCode()] = $attribute->getId();
            }
        }

        return $this->attributesById;
    }

    /**
     * @return Collection
     */
    public function getAttributeCollection()
    {
        return $this->attributeCollectionFactory->create();
    }
}
