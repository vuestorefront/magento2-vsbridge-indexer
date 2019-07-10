<?php
/**
 * @package  Divante\VsbridgeIndexerCms
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCms\Plugin\Indexer\Page\Save;

use Divante\VsbridgeIndexerCms\Model\Indexer\PageProcessor;
use Magento\Cms\Model\Page;

/**
 * Class UpdateCmsPage
 */
class UpdateCmsPage
{

    /**
     * @var PageProcessor
     */
    private $pageProcessor;

    /**
     * Save constructor.
     *
     * @param PageProcessor $pageProcessor
     */
    public function __construct(PageProcessor $pageProcessor)
    {
        $this->pageProcessor = $pageProcessor;
    }

    /**
     * @param Page $cmsPage
     * @param Page $result
     *
     * @return Page
     */
    public function afterAfterSave(Page $cmsPage, Page $result)
    {
        $result->getResource()->addCommitCallback(function () use ($cmsPage) {
            $this->pageProcessor->reindexRow($cmsPage->getId());
        });

        return $result;
    }

    /**
     * @param Page $cmsBlock
     * @param Page $result
     *
     * @return Page
     */
    public function afterAfterDeleteCommit(Page $cmsPage, Page $result)
    {
        $this->pageProcessor->reindexRow($cmsPage->getId());

        return $result;
    }
}
