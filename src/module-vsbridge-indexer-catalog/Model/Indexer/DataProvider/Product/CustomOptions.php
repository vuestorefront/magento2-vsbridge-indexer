<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types = 1);

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product;

use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeIndexerCatalog\Model\ProductOptionProcessor;
use Divante\VsbridgeIndexerCatalog\Model\ProductMetaData;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\CustomOptions as Resource;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\CustomOptionValues as OptionValuesResource;

/**
 * Class CustomOptions
 */
class CustomOptions implements DataProviderInterface
{
    /**
     * @var Resource
     */
    private $optionsResourceModel;

    /**
     * @var OptionValuesResource
     */
    private $optionValuesResourceModel;

    /**
     * @var ProductMetaData
     */
    private $productMetaData;

    /**
     * @var ProductOptionProcessor
     */
    private $productOptionProcessor;

    /**
     * CustomOptions constructor.
     *
     * @param Resource $resource
     * @param OptionValuesResource $customOptionValues
     * @param ProductOptionProcessor $processor
     * @param ProductMetaData $productMetaData
     */
    public function __construct(
        Resource $resource,
        OptionValuesResource $customOptionValues,
        ProductOptionProcessor $processor,
        ProductMetaData $productMetaData
    ) {
        $this->optionsResourceModel = $resource;
        $this->optionValuesResourceModel = $customOptionValues;
        $this->productMetaData = $productMetaData;
        $this->productOptionProcessor = $processor;
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $storeId = (int)$storeId;
        $linkField = $this->productMetaData->get()->getLinkField();
        $linkFieldIds = array_column($indexData, $linkField);

        $options = $this->optionsResourceModel->loadProductOptions($linkFieldIds, $storeId);
        $optionIds = array_column($options, 'option_id');
        $values = $this->optionValuesResourceModel->loadOptionValues($optionIds, $storeId);

        $optionsByProduct = $this->productOptionProcessor->prepareOptions($options, $values);

        foreach ($indexData as $productId => $productData) {
            $linkFieldValue = $productData[$linkField];

            if (isset($optionsByProduct[$linkFieldValue])) {
                $indexData[$productId]['custom_options'] = $optionsByProduct[$linkFieldValue];
            }
        }

        return $indexData;
    }
}
