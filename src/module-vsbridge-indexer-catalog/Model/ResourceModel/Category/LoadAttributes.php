<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category;

use Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Category\Attribute\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

/**
 * Class LoadAttributes
 */
class LoadAttributes
{
    /**
     * @var CollectionFactory
     */
    private $attributeCollectionFactory;

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
     * LoadAttributes constructor.
     *
     * @param CollectionFactory $attributeCollectionFactory
     */
    public function __construct(CollectionFactory $attributeCollectionFactory)
    {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * @return Attribute[]
     */
    public function execute()
    {
        return $this->initAttributes();
    }

    /**
     * @return Attribute[]
     */
    private function initAttributes()
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
     * @return Collection
     */
    private function getAttributeCollection()
    {
        return $this->attributeCollectionFactory->create();
    }
}
