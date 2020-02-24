<?php declare(strict_types=1);
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Config\Source;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class AbstractProductAttributeSource
 */
abstract class AbstractProductAttributeSource implements ArrayInterface
{
    /**
     * @var array|null
     */
    private $options;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * ProductAttributes constructor.
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (null === $this->options) {
            $this->options = [];
            $this->options[] = [
                'value' => '',
                'label' => __('-- All Attributes --'),
            ];
            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addVisibleFilter();
            $attributes = $collection->getItems();

            /** @var ProductAttributeInterface $attribute */
            foreach ($attributes as $attribute) {
                if ($this->canAddAttribute($attribute)) {
                    $this->options[] = [
                        'label' => $attribute->getAttributeCode(),
                        'value' => $attribute->getAttributeId(),
                    ];
                }
            }
        }

        return $this->options;
    }

    /**
     * @param ProductAttributeInterface $attribute
     *
     * @return bool
     */
    abstract public function canAddAttribute(ProductAttributeInterface $attribute): bool;
}
