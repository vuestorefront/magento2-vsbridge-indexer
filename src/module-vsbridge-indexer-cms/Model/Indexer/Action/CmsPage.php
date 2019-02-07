<?php
/**
 * @package   magento-2-1.dev
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCms\Model\Indexer\Action;

use Divante\VsbridgeIndexerCms\Model\ResourceModel\CmsPage as CmsPageResource;
use Magento\Cms\Model\Template\FilterProvider;

/**
 * Class CmsPage
 */
class CmsPage
{
    /**
     * @var CmsPageResource
     */
    private $resourceModel;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    private $filterProvider;

    /**
     * CmsBlock constructor.
     *
     * @param CmsPageResource $cmsBlockResource
     * @param FilterProvider $filterProvider
     */
    public function __construct(
        CmsPageResource $cmsBlockResource,
        FilterProvider $filterProvider
    ) {
        $this->filterProvider = $filterProvider;
        $this->resourceModel = $cmsBlockResource;
    }

    /**
     * @param int $storeId
     * @param array $pageIds
     *
     * @return \Traversable
     */
    public function rebuild($storeId = 1, array $pageIds = [])
    {
        $lastPageId = 0;

        do {
            $cmsPages = $this->resourceModel->loadPages($storeId, $pageIds, $lastPageId);

            foreach ($cmsPages as $pageData) {
                $lastPageId = $pageData['page_id'];
                $pageData['id'] = $pageData['page_id'];
                $pageData['content'] =
                    $this->filterProvider->getBlockFilter()->setStoreId($storeId)->filter($pageData['content']);
                $pageData['active'] = (bool)$pageData['is_active'];

                unset($pageData['creation_time'], $pageData['update_time'], $pageData['page_id']);
                unset($pageData['created_in']);
                unset($pageData['is_active'], $pageData['custom_theme'], $pageData['website_root']);

                yield $lastPageId => $pageData;
            }
        } while (!empty($cmsPages));
    }
}
