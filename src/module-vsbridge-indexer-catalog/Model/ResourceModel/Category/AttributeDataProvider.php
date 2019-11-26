<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category;

use Divante\VsbridgeIndexerCatalog\Index\Mapping\Category as CategoryMapping;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\AbstractEavAttributes;
use Divante\VsbridgeIndexerCore\Api\ConvertValueInterface;
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
     * @var LoadAttributes
     */
    private $loadAttributes;

    /**
     * AttributeDataProvider constructor.
     *
     * @param LoadAttributes $loadAttributes
     * @param CategoryMapping $categoryMapping
     * @param ResourceConnection $resourceConnection
     * @param ConvertValueInterface $castValue
     * @param MetadataPool $metadataPool
     * @param string $entityType
     */
    public function __construct(
        LoadAttributes $loadAttributes,
        CategoryMapping $categoryMapping,
        ResourceConnection $resourceConnection,
        ConvertValueInterface $castValue,
        MetadataPool $metadataPool,
        $entityType = \Magento\Catalog\Api\Data\CategoryInterface::class
    ) {
        $this->loadAttributes = $loadAttributes;

        parent::__construct($resourceConnection, $metadataPool, $castValue, $categoryMapping, $entityType);
    }

    /**
     * @param string $attributeCode
     *
     * @return Attribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeByCode($attributeCode)
    {
        return $this->loadAttributes->getAttributeByCode($attributeCode);
    }

    /**
     * @return Attribute[]
     */
    public function initAttributes()
    {
        return $this->loadAttributes->execute();
    }
}
