<?php
/**
 * @package   magento-2-1.dev
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCms\Model\Indexer\Action;

use Divante\VsbridgeIndexerCms\Model\ResourceModel\CmsBlock as CmsBlockResource;
use Magento\Cms\Model\Template\FilterProvider;

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
     * CmsBlock constructor.
     *
     * @param CmsBlockResource $cmsBlockResource
     * @param FilterProvider $filterProvider
     */
    public function __construct(
        CmsBlockResource $cmsBlockResource,
        FilterProvider $filterProvider
    ) {
        $this->filterProvider = $filterProvider;
        $this->resourceModel = $cmsBlockResource;
    }

    /**
     * @param int $storeId
     * @param array $blockIds
     *
     * @return \Traversable
     */
    public function rebuild($storeId = 1, array $blockIds = [])
    {
        $lastBlockId = 0;

        do {
            $cmsBlocks = $this->resourceModel->loadBlocks($storeId, $blockIds, $lastBlockId);

            foreach ($cmsBlocks as $blockData) {
                $lastBlockId = $blockData['block_id'];
                $blockData['id'] = $blockData['block_id'];
                $blockData['content'] =
                    $this->filterProvider->getBlockFilter()->setStoreId($storeId)->filter($blockData['content']);
                $blockData['active'] = (bool)$blockData['is_active'];

                unset($blockData['creation_time'], $blockData['update_time'], $blockData['block_id']);
                unset($blockData['created_in'], $blockData['updated_in']);
                unset($blockData['is_active']);

                yield $lastBlockId => $blockData;
            }
        } while (!empty($cmsBlocks));
    }
}
