<?php
/**
 * @package  magento-2-1.dev
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Plugin\Indexer\Category\Save;

use Divante\VsbridgeIndexerCatalog\Model\Indexer\CategoryProcessor;
use Magento\Catalog\Model\Category;

/**
 * Class UpdateCategoryDataPlugin
 */
class UpdateCategoryDataPlugin
{
    /**
     * @var CategoryProcessor
     */
    private $categoryProcessor;

    /**
     * UpdateProductData constructor.
     *
     * @param CategoryProcessor $processor
     */
    public function __construct(CategoryProcessor $processor)
    {
        $this->categoryProcessor = $processor;
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
        $this->categoryProcessor->reindexRow($category->getId());
    }
}
