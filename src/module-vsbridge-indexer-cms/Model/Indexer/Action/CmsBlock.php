<?php
/**
 * @package   Divante\VsbridgeIndexerCms
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCms\Model\Indexer\Action;

use Divante\VsbridgeIndexerCms\Model\ResourceModel\CmsBlock as CmsBlockResource;
use Divante\VsbridgeIndexerCore\Indexer\RebuildActionInterface;

/**
 * Class CmsBlock
 */
class CmsBlock implements RebuildActionInterface
{
    /**
     * @var CmsBlockResource
     */
    private $resourceModel;

    /**
     * CmsBlock constructor.
     *
     * @param CmsBlockResource $cmsBlockResource
     */
    public function __construct(
        CmsBlockResource $cmsBlockResource
    ) {
        $this->resourceModel = $cmsBlockResource;
    }

    /**
     * @param int $storeId
     * @param array $blockIds
     *
     * @return \Traversable
     */
    public function rebuild(int $storeId, array $blockIds): \Traversable
    {
        $lastBlockId = 0;

        do {
            $cmsBlocks = $this->resourceModel->loadBlocks($storeId, $blockIds, $lastBlockId);

            foreach ($cmsBlocks as $blockData) {
                $lastBlockId = (int)$blockData['block_id'];
                $blockData['id'] = $lastBlockId;
                $blockData['content'] = (string) $blockData['content'];
                $blockData['active'] = (bool)$blockData['is_active'];

                unset($blockData['creation_time'], $blockData['update_time'], $blockData['block_id']);
                unset($blockData['created_in'], $blockData['updated_in']);
                unset($blockData['is_active']);

                yield $lastBlockId => $blockData;
            }
        } while (!empty($cmsBlocks));
    }
}
