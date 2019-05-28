<?php
/**
 * @package  Divante\VsbridgeIndexerCms
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCms\Model;

use \Magento\Framework\Filter\Template as TemplateFilter;

/**
 * Class ContentProcessor
 */
class ContentProcessor
{
    /**
     * @param TemplateFilter $templateFilter
     * @param string $content
     *
     * @return string
     * @throws \Exception
     */
    public function parse(TemplateFilter $templateFilter, string $content)
    {
        return $templateFilter->filter($content);
    }
}
