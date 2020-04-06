<?php

declare(strict_types=1);

namespace Divante\VsbridgeIndexerCatalog\Model\Config\Source\Category;

use Magento\Catalog\Model\ResourceModel\Category\Attribute\Collection;
use Magento\Catalog\Model\ResourceModel\Category\Attribute\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class AbstractAttributeSource
 */
abstract class AbstractAttributeSource implements ArrayInterface
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
     * Attributes constructor.
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritDoc
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (null === $this->options) {
            $this->options = [
                [
                    'value' => '',
                    'label' => __('-- All Attributes --'),
                ]
            ];

            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();
            $attributes = $collection->getItems();

            /** @var Attribute $attribute */
            foreach ($attributes as $attribute) {
                if ($this->canAddAttribute($attribute)) {
                    $label = sprintf(
                        '%s (%s)',
                        $attribute->getDefaultFrontendLabel(),
                        $attribute->getAttributeCode()
                    );
                    $this->options[] = [
                        'label' => $label,
                        'value' => $attribute->getAttributeId(),
                    ];
                }
            }
        }

        return $this->options;
    }

    /**
     * Validate if attribute can be shown
     *
     * @param Attribute $attribute
     *
     * @return bool
     */
    abstract public function canAddAttribute(Attribute $attribute): bool;
}
