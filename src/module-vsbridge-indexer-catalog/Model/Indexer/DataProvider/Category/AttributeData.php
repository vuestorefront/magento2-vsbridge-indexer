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
use Divante\VsbridgeIndexerCatalog\Model\Attributes\CategoryAttributes;
use Divante\VsbridgeIndexerCatalog\Model\Attributes\CategoryChildAttributes;
use Divante\VsbridgeIndexerCatalog\Model\SystemConfig\CategoryConfigInterface;
use Divante\VsbridgeIndexerCatalog\Api\ApplyCategorySlugInterface;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category\AttributeDataProvider;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category\ProductCount as ProductCountResourceModel;
use Divante\VsbridgeIndexerCatalog\Api\DataProvider\Category\AttributeDataProviderInterface;

/**
 * Class AttributeData
 */
class AttributeData implements AttributeDataProviderInterface
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
     * @var CategoryAttributes
     */
    private $categoryAttributes;

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
     * @var CategoryConfigInterface
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
     * @param CategoryConfigInterface $configSettings
     * @param CategoryAttributes $categoryAttributes
     * @param CategoryChildAttributes $categoryChildAttributes
     * @param DataFilter $dataFilter
     */
    public function __construct(
        AttributeDataProvider $attributeResource,
        CategoryChildrenResource $childrenResource,
        ProductCountResourceModel $productCountResource,
        ApplyCategorySlugInterface $applyCategorySlug,
        CategoryConfigInterface $configSettings,
        CategoryAttributes $categoryAttributes,
        CategoryChildAttributes $categoryChildAttributes,
        DataFilter $dataFilter
    ) {
        $this->settings = $configSettings;
        $this->applyCategorySlug = $applyCategorySlug;
        $this->productCountResource = $productCountResource;
        $this->attributeResourceModel = $attributeResource;
        $this->childrenResourceModel = $childrenResource;
        $this->dataFilter = $dataFilter;
        $this->categoryAttributes = $categoryAttributes;
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
        $attributes = $this->attributeResourceModel->loadAttributesData(
            $storeId,
            $categoryIds,
            $this->categoryAttributes->getRequiredAttributes($storeId)
        );
        $productCount = $this->productCountResource->loadProductCount($categoryIds);

        foreach ($attributes as $entityId => $attributesData) {
            $categoryData = array_merge($indexData[$entityId], $attributesData);
            $categoryData = $this->prepareParentCategory($categoryData, $storeId);
            $categoryData = $this->addDefaultSortByOption($categoryData, $storeId);
            $categoryData = $this->addAvailableSortByOption($categoryData, $storeId);
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
                    $this->childAttributes->getRequiredAttributes($storeId)
                );

            $this->childrenProductCount = $this->productCountResource->loadProductCount(
                array_keys($groupedChildrenById)
            );
            $indexData[$categoryId] = $this->addChildrenData($categoryData, $groupedChildrenById, $storeId);
        }

        return $indexData;
    }

    /**
     * @param array $category
     * @param array $groupedChildren
     * @param int $storeId
     *
     * @return array
     */
    private function addChildrenData(array $category, array $groupedChildren, int $storeId)
    {
        $categoryId = $category['id'];
        $childrenData = $this->plotTree($groupedChildren, $categoryId, $storeId);

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
     * @param int $storeId
     *
     * @return array
     */
    private function plotTree(array $categories, int $rootId, int $storeId)
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
                $categoryData = $this->prepareChildCategory($categoryData, $storeId);
                $categoryData['children_data'] = $this->plotTree($categories, $categoryId, $storeId);
                $categoryData['children_count'] = count($categoryData['children_data']);
                $categoryTree[] = $categoryData;
            }
        }

        return empty($categoryTree) ? [] : $categoryTree;
    }

    /**
     * @param array $categoryDTO
     * @param int $storeId
     *
     * @return array
     */
    public function prepareParentCategory(array $categoryDTO, int $storeId)
    {
        return $this->prepareCategory($categoryDTO, $storeId);
    }

    /**
     * @param array $categoryDTO
     * @param int $storeId
     *
     * @return array
     */
    public function prepareChildCategory(array $categoryDTO, int $storeId)
    {
        return $this->prepareCategory($categoryDTO, $storeId);
    }

    /**
     * @param array $categoryDTO
     * @param int $storeId
     *
     * @return array
     */
    private function prepareCategory(array $categoryDTO, int $storeId)
    {
        $categoryDTO['id'] = (int)$categoryDTO['entity_id'];

        $categoryDTO = $this->addSlug($categoryDTO);

        if (!isset($categoryDTO['url_path'])) {
            $categoryDTO['url_path'] = $categoryDTO['slug'];
        } else {
            $categoryDTO['url_path'] .= $this->settings->getCategoryUrlSuffix($storeId);
        }

        $categoryDTO = array_diff_key($categoryDTO, array_flip($this->fieldsToRemove));
        $categoryDTO = $this->filterData($categoryDTO);

        return $categoryDTO;
    }

    /**
     * @param array $category
     * @param $storeId
     *
     * @return array
     */
    private function addAvailableSortByOption(array $category, $storeId): array
    {
        if (!$this->categoryAttributes->canAddAvailableSortBy($storeId)) {
            return $category;
        }

        if (isset($category['available_sort_by'])) {
            return $category;
        }

        $category['available_sort_by'] = $this->settings->getAttributesUsedForSortBy();

        return $category;
    }

    /**
     * @param array $category
     * @param int $storeId
     *
     * @return array
     */
    private function addDefaultSortByOption(array $category, $storeId): array
    {
        if (!$this->categoryAttributes->canAddDefaultSortBy($storeId)) {
            return $category;
        }

        if (isset($category['default_sort_by'])) {
            return $category;
        }

        $category['default_sort_by'] = $this->settings->getProductListDefaultSortBy($storeId);

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
