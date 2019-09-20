<?php
/**
 * @package   Divante\VsbridgeIndexerTax
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerTax\Model\Indexer\Action;

use Divante\VsbridgeIndexerTax\ResourceModel\Rules as RulesResourceModel;

/**
 * Class TaxRule
 */
class TaxRule
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
     * @param array $taxRuleIds
     *
     * @return \Traversable
     */
    public function rebuild(array $taxRuleIds = [])
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
