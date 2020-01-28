<?php declare(strict_types=1);
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

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
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addVisibleFilter();
            $attributes = $collection->getItems();

            foreach ($attributes as $attribute) {
                if ($this->canAddAttribute($attribute)) {
                    $this->options[] = [
                        'label' => $attribute->getName(),
                        'value' => $attribute->getAttributeId(),
                    ];
                }
            }
        }

        return $this->options;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute
     *
     * @return bool
     */
    abstract public function canAddAttribute(\Magento\Catalog\Api\Data\ProductAttributeInterface $attribute): bool;
}
