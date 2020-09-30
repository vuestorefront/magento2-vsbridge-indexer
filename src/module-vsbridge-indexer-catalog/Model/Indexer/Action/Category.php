<?php

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\Action;

use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category as ResourceModel;
use Divante\VsbridgeIndexerCore\Indexer\RebuildActionInterface;

/**
 * Class Category
 */
class Category implements RebuildActionInterface
{
    /**
     * @var ResourceModel
     */
    private $resourceModel;

    /**
     * Category constructor.
     *
     * @param ResourceModel $resourceModel
     */
    public function __construct(ResourceModel $resourceModel)
    {
        $this->resourceModel = $resourceModel;
    }

    /**
     * @param int $storeId
     * @param array $categoryIds
     *
     * @return \Generator
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function rebuild(int $storeId, array $categoryIds): \Traversable
    {
        $lastCategoryId = 0;

        if (!empty($categoryIds)) {
            $categoryIds = $this->resourceModel->getParentIds($categoryIds);
        }

        do {
            $categories = $this->resourceModel->getCategories($storeId, $categoryIds, $lastCategoryId);

            foreach ($categories as $category) {
                $lastCategoryId = $category['entity_id'];
                $category['id'] = (int)$category['entity_id'];

                yield $lastCategoryId => $category;
            }
        } while (!empty($categories));
    }
}
