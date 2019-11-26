<?php
/**
 * @package   Divante\VsbridgeIndexerTax
 * @author    Vladimir Plastovets <vladimir.plastovets@phoenix-media.eu>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerTax\Index\Mapping;

use Divante\VsbridgeIndexerCore\Api\Mapping\FieldInterface;
use Divante\VsbridgeIndexerCore\Api\MappingInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;

/**
 * Class Tax
 */
class Tax implements MappingInterface
{

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * CmsBlock constructor.
     *
     * @param EventManager $eventManager
     */
    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * @inheritdoc
     */
    public function getMappingProperties()
    {
        $properties = [
            'id'                     => ['type' => FieldInterface::TYPE_LONG],
            'code'                   => ['type' => FieldInterface::TYPE_TEXT],
            'position'               => ['type' => FieldInterface::TYPE_LONG],
            'priority'               => ['type' => FieldInterface::TYPE_TEXT],
            'calculate_subtotal'     => ['type' => FieldInterface::TYPE_TEXT],
            'product_tax_class_ids'  => ['type' => FieldInterface::TYPE_LONG],
            'customer_tax_class_ids' => ['type' => FieldInterface::TYPE_LONG]
        ];

        $properties['rates'] = [
            'properties' => [
                'id'             => ['type' => FieldInterface::TYPE_LONG],
                'code'           => ['type' => FieldInterface::TYPE_TEXT],
                'rate'           => ['type' => FieldInterface::TYPE_TEXT],
                'tax_postcode'   => ['type' => FieldInterface::TYPE_TEXT],
                'tax_region_id'  => ['type' => FieldInterface::TYPE_TEXT],
                'tax_country_id' => ['type' => FieldInterface::TYPE_TEXT]
            ]
        ];

        $mappingObject = new \Magento\Framework\DataObject();
        $mappingObject->setData('properties', $properties);

        $this->eventManager->dispatch(
            'elasticsearch_tax_mapping_properties',
            ['mapping' => $mappingObject]
        );

        return $mappingObject->getData();
    }
}
