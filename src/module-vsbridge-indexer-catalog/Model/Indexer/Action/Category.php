<?php
/**
 * @package   magento-2-1.dev
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\Action;

use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category as ResourceModel;

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

                yield $lastCategoryId => $categoryData;
            }
        } while (!empty($categories));
    }
}
