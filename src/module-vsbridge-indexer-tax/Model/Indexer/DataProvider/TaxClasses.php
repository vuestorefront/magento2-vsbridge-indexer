<?php
/**
 * @package   magento-2-1.dev
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerTax\Model\Indexer\DataProvider;

use Divante\VsbridgeIndexerTax\ResourceModel\TaxClasses as TaxClassesResourceModel;
use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;

/**
 * Class TaxClasses
 */
class TaxClasses implements DataProviderInterface
{

    /**
     * @var TaxClassesResourceModel
     */
    private $resourceModel;

    /**
     * @param TaxClassesResourceModel $resource
     */
    public function __construct(TaxClassesResourceModel $resource)
    {
        $this->resourceModel = $resource;
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $taxRuleIds = array_keys($indexData);
        $taxClasses = $this->resourceModel->loadTaxClasses($taxRuleIds);

        foreach ($taxClasses as $data) {
            $ruleId = $data['tax_calculation_rule_id'];
            $indexData[$ruleId]['customer_tax_class_ids'][] = (int)$data['customer_tax_class_id'];
            $indexData[$ruleId]['product_tax_class_ids'][] = (int)$data['product_tax_class_id'];
        }

        return $indexData;
    }
}
