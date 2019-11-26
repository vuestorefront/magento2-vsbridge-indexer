<?php
/**
 * @package  Divante\VsbridgeIndexerCms
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCms\Model;

use Divante\VsbridgeIndexerCms\Api\ContentProcessorInterface;
use Magento\Framework\Filter\Template as TemplateFilter;

/**
 * Class ContentProcessor
 */
class ContentProcessor implements ContentProcessorInterface
{

    /**
     * @inheritdoc
     */
    public function parse(TemplateFilter $templateFilter, string $content)
    {
        return $templateFilter->filter($content);
    }
}
