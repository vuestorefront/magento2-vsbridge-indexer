<?php
/**
 * @package   magento-2-1.dev
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\Action;

use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category as ResourceModel;
use Magento\Framework\Event\ManagerInterface as EventManager;

/**
 * Class Category
 */
class Category
{
    /**
     * @var ResourceModel
     */
    private $resourceModel;
    /**
     * @var EventManager $eventManager
     */
    private $eventManager;

    /**
     * Category constructor.
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
     * @param int $storeId
     * @param array $categoryIds
     *
     * @return \Generator
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function rebuild($storeId = 1, array $categoryIds = [])
    {
        $lastCategoryId = 0;

        if (!empty($categoryIds)) {
            $categoryIds = $this->resourceModel->getParentIds($categoryIds);
        }

        do {
            $categories = $this->resourceModel->getCategories($storeId, $categoryIds, $lastCategoryId);

            foreach ($categories as $category) {
                $lastCategoryId = $category['entity_id'];
                $categoryData['id'] = (int)$category['entity_id'];
                $categoryData = $category;

                $categoryDataObject = new \Magento\Framework\DataObject();
                $categoryDataObject->setData($categoryData);

                $this->eventManager->dispatch(
                    'elasticsearch_category_build_entity_data_after',
                    ['data_object' => $categoryData]
                );

                $categoryData = $categoryDataObject->getData();

                yield $lastCategoryId => $categoryData;
            }
        } while (!empty($categories));
    }
}
