<?php
/**
 * @package   Divante\VsbridgeIndexerCms
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCms\Model\Indexer\Action;

use Divante\VsbridgeIndexerCms\Model\ResourceModel\CmsPage as CmsPageResource;

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
     * CmsBlock constructor.
     *
     * @param CmsPageResource $cmsBlockResource
     */
    public function __construct(CmsPageResource $cmsBlockResource)
    {
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
                $lastPageId = (int)$pageData['page_id'];
                $pageData['id'] = $lastPageId;
                $pageData['content'] = $pageData['content'];
                $pageData['active'] = (bool)$pageData['is_active'];

                if (isset($pageData['sort_order'])) {
                    $pageData['sort_order'] = (int)$pageData['sort_order'];
                }

                unset($pageData['creation_time'], $pageData['update_time'], $pageData['page_id']);
                unset($pageData['created_in']);
                unset($pageData['is_active'], $pageData['custom_theme'], $pageData['website_root']);

                yield $lastPageId => $pageData;
            }
        } while (!empty($cmsPages));
    }
}
