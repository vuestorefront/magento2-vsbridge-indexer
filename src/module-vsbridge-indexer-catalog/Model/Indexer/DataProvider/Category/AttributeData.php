<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Category;

use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category\Children as CategoryChildrenResource;
use Divante\VsbridgeIndexerCore\Indexer\DataFilter;
use Divante\VsbridgeIndexerCatalog\Model\Attributes\CategoryChildAttributes;
use Divante\VsbridgeIndexerCatalog\Api\Data\CatalogConfigurationInterface;
use Divante\VsbridgeIndexerCatalog\Api\ApplyCategorySlugInterface;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category\AttributeDataProvider;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category\ProductCount as ProductCountResourceModel;

/**
 * Class AttributeData
 */
class AttributeData
{
    /**
     * List of fields from category
     *
     * @var array
     */
    private $fieldsToRemove = [
        'row_id',
        'created_in',
        'updated_in',
        'entity_id',
        'entity_type_id',
        'attribute_set_id',
        'all_children',
        'created_at',
        'updated_at',
        'request_path',
    ];

    /**
     * @var CategoryChildAttributes
     */
    private $childAttributes;

    /**
     * @var AttributeDataProvider
     */
    private $attributeResourceModel;

    /**
     * @var CategoryChildrenResource
     */
    private $childrenResourceModel;

    /**
     * @var ProductCountResourceModel
     */
    private $productCountResource;

    /**
     * @var \Divante\VsbridgeIndexerCore\Indexer\DataFilter
     */
    private $dataFilter;

    /**
     * @var array
     */
    private $childrenRowAttributes = [];

    /**
     * @var array
     */
    private $childrenProductCount = [];

    /**
     * @var CatalogConfigurationInterface
     */
    private $settings;

    /**
     * @var ApplyCategorySlugInterface
     */
    private $applyCategorySlug;

    /**
     * AttributeData constructor.
     *
     * @param AttributeDataProvider $attributeResource
     * @param CategoryChildrenResource $childrenResource
     * @param ProductCountResourceModel $productCountResource
     * @param ApplyCategorySlugInterface $applyCategorySlug
     * @param CatalogConfigurationInterface $configSettings
     * @param CategoryChildAttributes $categoryChildAttributes
     * @param DataFilter $dataFilter
     */
    public function __construct(
        AttributeDataProvider $attributeResource,
        CategoryChildrenResource $childrenResource,
        ProductCountResourceModel $productCountResource,
        ApplyCategorySlugInterface $applyCategorySlug,
        CatalogConfigurationInterface $configSettings,
        CategoryChildAttributes $categoryChildAttributes,
        DataFilter $dataFilter
    ) {
        $this->settings = $configSettings;
        $this->applyCategorySlug = $applyCategorySlug;
        $this->productCountResource = $productCountResource;
        $this->attributeResourceModel = $attributeResource;
        $this->childrenResourceModel = $childrenResource;
        $this->dataFilter = $dataFilter;
        $this->childAttributes = $categoryChildAttributes;
    }

    /**
     * @param array $indexData
     * @param int   $storeId
     *
     * @return array
     */
    public function addData(array $indexData, $storeId)
    {
        $this->settings->getAttributesUsedForSortBy();
        /**
         * TODO add option to load only specific categories
         */

        $categoryIds = array_keys($indexData);
        $attributes = $this->attributeResourceModel->loadAttributesData($storeId, $categoryIds);
        $productCount = $this->productCountResource->loadProductCount($categoryIds);

        foreach ($attributes as $entityId => $attributesData) {
            $categoryData = array_merge($indexData[$entityId], $attributesData);
            $categoryData = $this->prepareCategory($categoryData);
            $categoryData = $this->addSortOptions($categoryData, $storeId);
            $categoryData['product_count'] = $productCount[$entityId];

            $indexData[$entityId] = $categoryData;
        }

        foreach ($indexData as $categoryId => $categoryData) {
            $children = $this->childrenResourceModel->loadChildren($categoryData, $storeId);
            $groupedChildrenById = $this->groupChildrenById($children);
            unset($children);

            $this->childrenRowAttributes =
                $this->attributeResourceModel->loadAttributesData(
                    $storeId,
                    array_keys($groupedChildrenById),
                    $this->childAttributes->getRequiredAttributes()
                );

            $this->childrenProductCount = $this->productCountResource->loadProductCount(
                array_keys($groupedChildrenById)
            );
            $indexData[$categoryId] = $this->addChildrenData($categoryData, $groupedChildrenById);
        }

        return $indexData;
    }

    /**
     * @param array $category
     * @param array $groupedChildren
     *
     * @return array
     */
    private function addChildrenData(array $category, array $groupedChildren)
    {
        $categoryId = $category['id'];
        $childrenData = $this->plotTree($groupedChildren, $categoryId);

        $category['children_data'] = $childrenData;
        $category['children_count'] = count($childrenData);

        return $category;
    }

    /**
     * @param array $children
     *
     * @return array
     */
    private function groupChildrenById(array $children)
    {
        $sortChildrenById = [];

        foreach ($children as $cat) {
            $sortChildrenById[$cat['entity_id']] = $cat;
            $sortChildrenById[$cat['entity_id']]['children_data'] = [];
        }

        return $sortChildrenById;
    }

    /**
     * @param array $categories
     * @param int $rootId
     *
     * @return array
     */
    private function plotTree(array $categories, $rootId)
    {
        $categoryTree = [];

        foreach ($categories as $categoryId => $categoryData) {
            $parent = $categoryData['parent_id'];

            # A direct child is found
            if ($parent == $rootId) {
                # Remove item from tree (we don't need to traverse this again)
                unset($categories[$categoryId]);

                if (isset($this->childrenRowAttributes[$categoryId])) {
                    $categoryData = array_merge($categoryData, $this->childrenRowAttributes[$categoryId]);
                }

                $categoryData['product_count'] = $this->childrenProductCount[$categoryId];
                $categoryData = $this->prepareCategory($categoryData);
                $categoryData['children_data'] = $this->plotTree($categories, $categoryId);
                $categoryData['children_count'] = count($categoryData['children_data']);
                $categoryTree[] = $categoryData;
            }
        }

        return empty($categoryTree) ? [] : $categoryTree;
    }

    /**
     * @param array $categoryDTO
     *
     * @return array
     */
    private function prepareCategory(array $categoryDTO)
    {
        $categoryDTO['id'] = (int)$categoryDTO['entity_id'];

        $categoryDTO = $this->addSlug($categoryDTO);

        if (!isset($categoryDTO['url_path'])) {
            $categoryDTO['url_path'] = $categoryDTO['slug'];
        }

        $categoryDTO = array_diff_key($categoryDTO, array_flip($this->fieldsToRemove));
        $categoryDTO = $this->filterData($categoryDTO);

        return $categoryDTO;
    }

    /**
     * @param array $category
     * @param int $storeId
     *
     * @return array
     */
    private function addSortOptions(array $category, $storeId)
    {
        if (!isset($category['available_sort_by'])) {
            $category['available_sort_by'] = $this->settings->getAttributesUsedForSortBy();
        }

        if (!isset($category['default_sort_by'])) {
            $category['default_sort_by'] = $this->settings->getProductListDefaultSortBy($storeId);
        }

        return $category;
    }

    /**
     * @param array $categoryDTO
     *
     * @return array
     */
    private function addSlug(array $categoryDTO)
    {
        return $this->applyCategorySlug->execute($categoryDTO);
    }

    /**
     * @param array $categoryData
     *
     * @return array
     */
    private function filterData(array $categoryData)
    {
        return $this->getDataFilter()->execute($categoryData);
    }

    /**
     * @return DataFilter
     */
    private function getDataFilter()
    {
        return $this->dataFilter;
    }
}
