<?php
/**
 * @package  Divante\VsbridgeIndexerCms
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCms\Api;

use Magento\Framework\Filter\Template as TemplateFilter;

/**
 * Interface ContentProcessorInterface
 */
interface ContentProcessorInterface
{
    /**
     * @param TemplateFilter $templateFilter
     * @param string $content
     *
     * @return string|array
     * @throws \Exception
     */
    public function parse(TemplateFilter $templateFilter, string $content);
}
