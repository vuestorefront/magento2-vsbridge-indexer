<?php declare(strict_types=1);
/**
 * @package Divante\VsbridgeIndexerCatalog
 * @author Marek Mularczyk <mmularczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Plugin\Controller\Catalog\Adminhtml\Product\Action;

use Divante\VsbridgeIndexerCatalog\Model\Indexer\ProductProcessor;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute;

/**
 * Class AttributeSavePlugin
 */
class AttributeSavePlugin
{
    /**
     * @var Attribute
     */
    private $attributeHelper;

    /**
     * @var ProductProcessor
     */
    private $processor;

    /**
     * AttributeSavePlugin constructor.
     *
     * @param Attribute $attributeHelper
     * @param ProductProcessor $processor
     */
    public function __construct(Attribute $attributeHelper, ProductProcessor $processor)
    {
        $this->attributeHelper = $attributeHelper;
        $this->processor = $processor;
    }

    /**
     * @param Save $subject
     * @param Redirect $result
     *
     * @return Redirect
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(Save $subject, $result)
    {
        $productIds = $this->attributeHelper->getProductIds();

        $this->processor->reindexList($productIds);

        return $result;
    }
}
