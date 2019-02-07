<?php
/**
 * @package   magento-2-1.dev
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerTax\Model\Indexer\DataProvider;

use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeIndexerTax\ResourceModel\Rates as RatesResourceModel;

/**
 * Class TaxRates
 */
class TaxRates implements DataProviderInterface
{
    /**
     * @var RatesResourceModel
     */
    private $resourceModel;

    /**
     * @param RatesResourceModel $resource
     */
    public function __construct(RatesResourceModel $resource)
    {
        $this->resourceModel = $resource;
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        $taxRuleIds = array_keys($indexData);
        $taxRates = $this->resourceModel->loadTaxRates($taxRuleIds);

        foreach ($taxRates as $taxRate) {
            $ruleId = $taxRate['tax_calculation_rule_id'];
            $taxRate['id'] = (int)($taxRate['tax_calculation_rate_id']);
            unset($taxRate['tax_calculation_rule_id'], $taxRate['tax_calculation_rate_id']);
            $indexData[$ruleId]['rates'][] = $taxRate;
        }

        return $indexData;
    }
}
