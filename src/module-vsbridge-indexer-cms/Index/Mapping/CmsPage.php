<?php
/**
 * @package   magento-2-1.dev
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCms\Index\Mapping;

use Divante\VsbridgeIndexerCore\Api\MappingInterface;
use Divante\VsbridgeIndexerCore\Api\Mapping\FieldInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;

/**
 * Class CmsPage
 */
class CmsPage implements MappingInterface
{

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $textFields = [
        'title',
        'content',
        'content_heading',
        'meta_keywords',
        'meta_description',
        'custom_layout_update_xml',
        'custom_root_template',
        'layout_update_xml',
    ];

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
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function getMappingProperties()
    {
        $properties = [
            'id' => ['type' => FieldInterface::TYPE_LONG],
            'active' => ['type' => FieldInterface::TYPE_BOOLEAN],
            'sort_order' => ['type' => FieldInterface::TYPE_LONG],
            //compatible with product/category attribute mapping
            'page_layout' => ['type' => FieldInterface::TYPE_KEYWORD],
            'identifier' => ['type' => FieldInterface::TYPE_KEYWORD],
        ];

        foreach ($this->textFields as $field) {
            $properties[$field] = ['type' => FieldInterface::TYPE_TEXT];
        }

        $mappingObject = new \Magento\Framework\DataObject();
        $mappingObject->setData('properties', $properties);

        $this->eventManager->dispatch(
            'elasticsearch_cms_page_mapping_properties',
            ['mapping' => $mappingObject]
        );

        return $mappingObject->getData();
    }
}
