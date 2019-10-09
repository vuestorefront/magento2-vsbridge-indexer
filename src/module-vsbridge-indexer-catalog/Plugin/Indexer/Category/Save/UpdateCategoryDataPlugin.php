<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Plugin\Indexer\Category\Save;

use Divante\VsbridgeIndexerCatalog\Model\Indexer\CategoryProcessor;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category as CategoryResourceModel;

use Magento\Catalog\Model\Category;

/**
 * Class UpdateCategoryDataPlugin
 */
class UpdateCategoryDataPlugin
{
    /**
     * @var CategoryResourceModel
     */
    private $resourceModel;

    /**
     * @var CategoryProcessor
     */
    private $categoryProcessor;

    /**
     * UpdateCategoryDataPlugin constructor.
     *
     * @param CategoryResourceModel $resourceModel
     * @param CategoryProcessor $processor
     */
    public function __construct(
        CategoryResourceModel $resourceModel,
        CategoryProcessor $processor
    ) {
        $this->categoryProcessor = $processor;
        $this->resourceModel = $resourceModel;
    }

    /**
     * Reindex data after product save/delete resource commit
     *
     * @param Category $category
     *
     * @return void
     */
    public function afterReindex(Category $category)
    {
        $categoryIds = [];
        $originalUrlKey = $category->getOrigData('url_key');
        $urlKey = $category->getData('url_key');
        $categoryId = (int) $category->getId();

        if (!$category->isObjectNew() && $originalUrlKey !== $urlKey) {
            $categoryIds = $this->resourceModel->getAllSubCategories($categoryId);
        }

        $categoryIds[] = $categoryId;

        $this->categoryProcessor->reindexList($categoryIds);
    }
}
