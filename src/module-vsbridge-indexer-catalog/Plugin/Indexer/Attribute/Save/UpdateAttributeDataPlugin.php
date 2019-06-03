<?php
/**
 * @package  magento-2-1.dev
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Plugin\Indexer\Attribute\Save;

use Divante\VsbridgeIndexerCatalog\Model\Indexer\AttributeProcessor;
use Divante\VsbridgeIndexerCatalog\Model\Indexer\ProductProcessor;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

/**
 * Class UpdateAttributeDataPlugin
 */
class UpdateAttributeDataPlugin
{
    /**
     * @var AttributeProcessor
     */
    private $attributeProcessor;

    /**
     * @var ProductProcessor
     */
    private $productProcessor;

    /**
     * UpdateAttributeData constructor.
     *
     * @param ProductProcessor $processor
     * @param AttributeProcessor $attributeProcessor
     */
    public function __construct(
        ProductProcessor $processor,
        AttributeProcessor $attributeProcessor
    ) {
        $this->productProcessor = $processor;
        $this->attributeProcessor = $attributeProcessor;
    }

    /**
     * TODO check if we add new attribute, after adding new attribute send request to elastic to add new mapping
     * for field.
     * @param Attribute $attribute
     *
     * @return Attribute
     */
    public function afterAfterSave(Attribute $attribute)
    {
        $this->attributeProcessor->reindexRow($attribute->getId());

        return $attribute;
    }

    /**
     * After deleting attribute we should update all products
     * @param Attribute $attribute
     * @param Attribute $result
     *
     * @return Attribute
     */
    public function afterAfterDeleteCommit(Attribute $attribute, Attribute $result)
    {
        $this->attributeProcessor->reindexRow($attribute->getId());
        $this->productProcessor->markIndexerAsInvalid();

        return $result;
    }
}
