<?php
/**
 * @package   Divante\VsbridgeIndexerCms
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCms\Model\Indexer\Action;

use Divante\VsbridgeIndexerCms\Api\ContentProcessorInterface;
use Divante\VsbridgeIndexerCms\Model\ResourceModel\CmsBlock as CmsBlockResource;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;

/**
 * Class CmsBlock
 */
class CmsBlock
{
    /**
     * @var CmsBlockResource
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
     * @param CmsBlockResource $cmsBlockResource
     * @param FilterProvider $filterProvider
     */
    public function __construct(
        AreaList $areaList,
        ContentProcessorInterface $contentProcessor,
        CmsBlockResource $cmsBlockResource,
        FilterProvider $filterProvider
    ) {
        $this->areaList = $areaList;
        $this->filterProvider = $filterProvider;
        $this->resourceModel = $cmsBlockResource;
        $this->contentProcessor = $contentProcessor;
    }

    /**
     * @param int $storeId
     * @param array $blockIds
     *
     * @return \Traversable
     */
    public function rebuild($storeId = 1, array $blockIds = [])
    {
        $this->areaList->getArea(Area::AREA_FRONTEND)->load(Area::PART_DESIGN);
        $templateFilter = $this->filterProvider->getBlockFilter()->setStoreId($storeId);
        $lastBlockId = 0;

        do {
            $cmsBlocks = $this->resourceModel->loadBlocks($storeId, $blockIds, $lastBlockId);

            foreach ($cmsBlocks as $blockData) {
                $lastBlockId = (int)$blockData['block_id'];
                $blockData['id'] = $lastBlockId;
                $blockData['content'] = $this->contentProcessor->parse($templateFilter, (string) $blockData['content']);
                $blockData['active'] = (bool)$blockData['is_active'];

                unset($blockData['creation_time'], $blockData['update_time'], $blockData['block_id']);
                unset($blockData['created_in'], $blockData['updated_in']);
                unset($blockData['is_active']);

                yield $lastBlockId => $blockData;
            }
        } while (!empty($cmsBlocks));
    }
}
