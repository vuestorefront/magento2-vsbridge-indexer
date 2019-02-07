<?php
/**
 * @package  magento-2-1.dev
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCms\Plugin\Indexer\Block\Save;

use Divante\VsbridgeIndexerCms\Model\Indexer\BlockProcessor;
use Magento\Cms\Model\Block;

/**
 * Class UpdateCmsBlock
 */
class UpdateCmsBlock
{

    /**
     * @var BlockProcessor
     */
    private $blockProcessor;

    /**
     * Save constructor.
     *
     * @param BlockProcessor $blockProcessor
     */
    public function __construct(BlockProcessor $blockProcessor)
    {
        $this->blockProcessor = $blockProcessor;
    }

    /**
     * @param Block $cmsBlock
     * @param Block $result
     *
     * @return Block
     */
    public function afterAfterSave(Block $cmsBlock, Block $result)
    {
        $result->getResource()->addCommitCallback(function () use ($cmsBlock) {
            $this->blockProcessor->reindexRow($cmsBlock->getId());
        });

        return $result;
    }

    /**
     * @param Block $cmsBlock
     * @param Block $result
     *
     * @return Block
     */
    public function afterAfterDeleteCommit(Block $cmsBlock, Block $result)
    {
        $this->blockProcessor->reindexRow($cmsBlock->getId());

        return $result;
    }
}
