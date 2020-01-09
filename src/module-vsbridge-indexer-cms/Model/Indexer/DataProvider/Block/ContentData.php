<?php declare(strict_types = 1);
/**
 * @package   Divante\VsbridgeIndexerCms
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCms\Model\Indexer\DataProvider\Block;

use Divante\VsbridgeIndexerCms\Model\Indexer\DataProvider\CmsContentFilter;
use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;

/**
 * Class ContentData
 */
class ContentData implements DataProviderInterface
{
    /**
     * @var CmsContentFilter
     */
    private $cmsContentFilter;

    /**
     * ContentData constructor.
     *
     * @param CmsContentFilter $cmsContentFilter
     */
    public function __construct(CmsContentFilter $cmsContentFilter)
    {
        $this->cmsContentFilter = $cmsContentFilter;
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        return $this->cmsContentFilter->filter($indexData, $storeId, 'block');
    }
}
