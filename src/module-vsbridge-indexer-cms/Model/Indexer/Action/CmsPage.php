<?php
/**
 * @package   Divante\VsbridgeIndexerCms
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCms\Model\Indexer\Action;

use Divante\VsbridgeIndexerCms\Api\ContentProcessorInterface;
use Divante\VsbridgeIndexerCms\Model\ResourceModel\CmsPage as CmsPageResource;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;

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
     * @var AreaList
     */
    private $areaList;

    /**
     * @var ContentProcessorInterface
     */
    private $contentProcessor;

    /**
     * CmsBlock constructor.
     *
     * @param AreaList $areaList
     * @param ContentProcessorInterface $contentProcessor
     * @param CmsPageResource $cmsBlockResource
     * @param FilterProvider $filterProvider
     */
    public function __construct(
        AreaList $areaList,
        ContentProcessorInterface $contentProcessor,
        CmsPageResource $cmsBlockResource,
        FilterProvider $filterProvider
    ) {
        $this->areaList = $areaList;
        $this->resourceModel = $cmsBlockResource;
        $this->filterProvider = $filterProvider;
        $this->contentProcessor = $contentProcessor;
    }

    /**
     * @param int $storeId
     * @param array $pageIds
     *
     * @return \Traversable
     */
    public function rebuild($storeId = 1, array $pageIds = [])
    {
        $this->areaList->getArea(Area::AREA_FRONTEND)->load(Area::PART_DESIGN);
        $templateFilter = $this->filterProvider->getPageFilter()->setStoreId($storeId);
        $lastPageId = 0;

        do {
            $cmsPages = $this->resourceModel->loadPages($storeId, $pageIds, $lastPageId);

            foreach ($cmsPages as $pageData) {
                $lastPageId = (int)$pageData['page_id'];
                $pageData['id'] = $lastPageId;
                $pageData['content'] = $this->contentProcessor->parse($templateFilter, (string) $pageData['content']);
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
