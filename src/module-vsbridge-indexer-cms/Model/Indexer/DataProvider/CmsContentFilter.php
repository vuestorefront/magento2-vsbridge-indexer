<?php declare(strict_types = 1);
/**
 * @package   Divante\VsbridgeIndexerCms
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCms\Model\Indexer\DataProvider;

use Divante\VsbridgeIndexerCms\Api\ContentProcessorInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Store\Model\App\Emulation;

/**
 * Class AbstractContentData
 */
class CmsContentFilter
{
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    private $filterProvider;

    /**
     * @var AreaList
     */
    private $areaList;

    /**
     * @var Emulation
     */
    private $appEmulation;

    /**
     * @var ContentProcessorInterface
     */
    private $contentProcessor;

    /**
     * CmsBlock constructor.
     *
     * @param AreaList $areaList
     * @param Emulation $emulation
     * @param ContentProcessorInterface $contentProcessor
     * @param FilterProvider $filterProvider
     */
    public function __construct(
        AreaList $areaList,
        Emulation $emulation,
        ContentProcessorInterface $contentProcessor,
        FilterProvider $filterProvider
    ) {
        $this->areaList = $areaList;
        $this->appEmulation = $emulation;
        $this->filterProvider = $filterProvider;
        $this->contentProcessor = $contentProcessor;
    }

    /**
     * @param array $indexData
     * @param int $storeId
     * @param string $type
     *
     * @return array
     * @throws Exception
     */
    public function filter(array $indexData, int $storeId, string $type)
    {
        $this->appEmulation->startEnvironmentEmulation($storeId);
        $this->areaList->getArea(Area::AREA_FRONTEND)->load(Area::PART_DESIGN);
        $templateFilter = $this->geTemplateFilter($type)->setStoreId($storeId);

        foreach ($indexData as &$cms) {
            $cms['content'] = $this->contentProcessor->parse($templateFilter, (string) $cms['content']);
        }

        $this->appEmulation->stopEnvironmentEmulation();

        return $indexData;
    }

    /**
     * @param string $type
     *
     * @return \Magento\Framework\Filter\Template
     */
    private function geTemplateFilter(string $type): \Magento\Framework\Filter\Template
    {
        return $type === 'block' ? $this->filterProvider->getBlockFilter() : $this->filterProvider->getPageFilter();
    }
}
