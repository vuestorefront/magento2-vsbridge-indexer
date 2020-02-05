<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Divante\VsbridgeIndexerCatalog\Model\Attributes\ConfigurableAttributes;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product as Resource;

/**
 * Class ConfigurableChildAttributes
 */
class ConfigurableChildAttributes extends AbstractProductAttributeSource
{

    /**
     * @var
     */
    private $restrictedAttributes;

    /**
     * @var Resource
     */
    private $productResource;

    /**
     * ProductAttributes constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param Resource $productResource
     */
    public function __construct(CollectionFactory $collectionFactory, Resource $productResource)
    {
        $this->productResource = $productResource;

        parent::__construct($collectionFactory);
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     *
     * @return bool
     */
    public function canAddAttribute(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute): bool
    {
        if (in_array($attribute->getAttributeCode(), $this->getRestrictedAttributes())) {
            return false;
        }

        if (in_array($attribute->getAttributeId(), $this->productResource->getConfigurableAttributeIds())) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    private function getRestrictedAttributes()
    {
        if (null === $this->restrictedAttributes) {
            $this->restrictedAttributes = array_merge(
                ProductAttributes::GENERAL_RESTRICTED_ATTRIBUTES,
                ConfigurableAttributes::MINIMAL_ATTRIBUTE_SET
            );
        }

        return $this->restrictedAttributes;
    }
}
