<?php
/**
 * @package   magento-2-1.dev
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\Action;

use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Attribute as ResourceModel;
use Magento\Framework\Event\ManagerInterface as EventManager;

/**
 * Class Attribute
 */
class Attribute
{
    /**
     * @var ResourceModel
     */
    private $resourceModel;

    /**
     * Attribute constructor.
     *
     * @param ResourceModel $resourceModel
     * @param EventManager  $eventManager
     */
    public function __construct(
        ResourceModel $resourceModel,
        EventManager $eventManager
    ) {
        $this->resourceModel = $resourceModel;
        $this->eventManager = $eventManager;
    }

    /**
     * @param array $attributeIds
     *
     * @return \Traversable
     */
    public function rebuild(array $attributeIds = [])
    {
        $lastAttributeId = 0;

        do {
            $attributes = $this->resourceModel->getAttributes($attributeIds, $lastAttributeId);

            foreach ($attributes as $attributeData) {
                $lastAttributeId = $attributeData['attribute_id'];
                $attributeData['id'] = $attributeData['attribute_id'];
                $attributeData = $this->filterData($attributeData);

                $attributeDataObject = new \Magento\Framework\DataObject();
                $attributeDataObject->setData($attributeData);

                $this->eventManager->dispatch(
                    'elasticsearch_attribute_build_entity_data_after',
                    ['data_object' => $attributeDataObject]
                );

                $attributeData = $attributeDataObject->getData();

                yield $lastAttributeId => $attributeData;
            }
        } while (!empty($attributes));
    }

    /**
     * @param array $attributeData
     *
     * @return array
     */
    private function filterData(array $attributeData)
    {
        if (isset($attributeData['position'])) {
            $attributeData['position'] = (int)$attributeData['position'];
        }

        return $attributeData;
    }
}
