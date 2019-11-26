<?php

/**
 * @package  Divante\VsbridgeIndexerReview
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerReview\Index\Mapping;

use Divante\VsbridgeIndexerCore\Api\Mapping\FieldInterface;
use Divante\VsbridgeIndexerCore\Api\MappingInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class Review
 */
class Review implements MappingInterface
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
            'created_at' => [
                'type' => FieldInterface::TYPE_DATE,
                'format' => FieldInterface::DATE_FORMAT,
            ],
            'id' => ['type' => FieldInterface::TYPE_LONG],
            'product_id' => ['type' => FieldInterface::TYPE_LONG],
            'title' => ['type' => FieldInterface::TYPE_TEXT],
            'detail' => ['type' => FieldInterface::TYPE_TEXT],
            'nickname' => ['type' => FieldInterface::TYPE_TEXT],
            'review_status' => ['type' => FieldInterface::TYPE_INTEGER],
            'customer_id' => ['type' => FieldInterface::TYPE_INTEGER],
            'ratings' => $this->getRatingMapping(),
        ];

        $mappingObject = new \Magento\Framework\DataObject();
        $mappingObject->setData('properties', $properties);

        $this->eventManager->dispatch(
            'elasticsearch_review_mapping_properties',
            ['mapping' => $mappingObject]
        );

        return $mappingObject->getData();
    }

    /**
     * @return array
     */
    private function getRatingMapping(): array
    {
        return [
            'properties' => [
                'title' => ['type' => FieldInterface::TYPE_TEXT],
                'percent' => ['type' => FieldInterface::TYPE_SHORT],
                'value' => ['type' => FieldInterface::TYPE_SHORT],
            ]
        ];
    }
}
