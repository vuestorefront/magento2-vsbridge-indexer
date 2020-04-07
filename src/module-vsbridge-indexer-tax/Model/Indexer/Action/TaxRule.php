<?php

namespace Divante\VsbridgeIndexerTax\Model\Indexer\Action;

use Divante\VsbridgeIndexerTax\ResourceModel\Rules as RulesResourceModel;

use Divante\VsbridgeIndexerCore\Indexer\RebuildActionInterface;

/**
 * Class TaxRule
 */
class TaxRule implements RebuildActionInterface
{
    /**
     * @var RulesResourceModel
     */
    private $resourceModel;

    /**
     * Divante_vsbridgeIndexer_Model_Indexer_Action_Taxrule constructor.
     */
    public function __construct(RulesResourceModel $resource)
    {
        $this->resourceModel = $resource;
    }

    /**
     * @param int $storeId
     * @param array $taxRuleIds
     *
     * @return \Traversable
     */
    public function rebuild(int $storeId, array $taxRuleIds): \Traversable
    {
        $lastTaxRuleId = 0;

        do {
            $taxRules = $this->resourceModel->getTaxRules($taxRuleIds, $lastTaxRuleId);

            foreach ($taxRules as $taxRule) {
                $taxRule['id'] = (int)($taxRule['tax_calculation_rule_id']);
                unset($taxRule['tax_calculation_rule_id']);
                $lastTaxRuleId = $taxRule['id'];

                yield $lastTaxRuleId => $taxRule;
            }
        } while (!empty($taxRules));
    }
}
