<?php

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Category;

use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category\Children as CategoryChildrenResource;
use Divante\VsbridgeIndexerCore\Indexer\DataFilter;
use Divante\VsbridgeIndexerCatalog\Model\Attributes\CategoryChildAttributes;
use Divante\VsbridgeIndexerCatalog\Model\ConfigSettings;
use Divante\VsbridgeIndexerCatalog\Model\SlugGenerator;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Category\AttributeDataProvider;

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
     * @var \Divante\VsbridgeIndexerCore\Indexer\DataFilter
     */
    private $dataFilter;

    /**
     * @var array
     */
    private $childrenRowAttributes = [];

    /**
     * @var ConfigSettings
     */
    private $settings;

    /**
     * @var SlugGenerator
     */
    private $catalogHelper;

    /**
     * AttributeData constructor.
     *
     * @param AttributeDataProvider $attributeResource
     * @param CategoryChildrenResource $childrenResource
     * @param SlugGenerator\Proxy $catalogHelper
     * @param ConfigSettings $configSettings
     * @param CategoryChildAttributes $categoryChildAttributes
     * @param DataFilter $dataFilter
     */
    public function __construct(
        AttributeDataProvider $attributeResource,
        CategoryChildrenResource $childrenResource,
        SlugGenerator\Proxy $catalogHelper,
        ConfigSettings $configSettings,
        CategoryChildAttributes $categoryChildAttributes,
        DataFilter $dataFilter
    ) {
        $this->settings = $configSettings;
        $this->catalogHelper = $catalogHelper;
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
        /**
         * TODO add option to load only specific categories
         */
        $attributes = $this->attributeResourceModel->loadAttributesData($storeId, array_keys($indexData));

        foreach ($attributes as $entityId => $attributesData) {
            $categoryData = array_merge($indexData[$entityId], $attributesData);
            $indexData[$entityId] = $this->prepareCategory($categoryData);
        }

        foreach ($indexData as $categoryId => $categoryData) {
            $children = $this->childrenResourceModel->loadChildren($categoryData, $storeId);
            $sortChildrenById = $this->sortChildrenById($children);
            unset($children);

            $this->childrenRowAttributes =
                $this->attributeResourceModel->loadAttributesData(
                    $storeId,
                    array_keys($sortChildrenById),
                    $this->childAttributes->getRequiredAttributes()
                );

            $childrenData = $this->plotTree($sortChildrenById, $categoryId);

            $indexData[$categoryId]['children_data'] = $childrenData;
            $indexData[$categoryId]['children_count'] = count($childrenData);
        }

        return $indexData;
    }

    /**
     * @param array $children
     *
     * @return array
     */
    private function sortChildrenById(array $children)
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
     * @param       $rootId
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
     * @param array $categoryDTO
     *
     * @return array
     */
    private function addSlug(array $categoryDTO)
    {
        if ($this->settings->useMagentoUrlKeys()) {
            if (!isset($categoryDTO['url_key'])) {
                $slug = $this->catalogHelper->generate(
                    $categoryDTO['name'],
                    $categoryDTO['entity_id']
                );
                $categoryDTO['url_key'] = $slug;
            }

            $categoryDTO['slug'] = $categoryDTO['url_key'];
        } else {
            $slug = $this->catalogHelper->generate($categoryDTO['name'], $categoryDTO['entity_id']);
            $categoryDTO['slug'] = $slug;
        }

        return $categoryDTO;
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
