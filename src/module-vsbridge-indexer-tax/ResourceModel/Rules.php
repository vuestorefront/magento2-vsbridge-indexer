<?php
/**
 * @package   Divante\VsbridgeIndexerTax
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerTax\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Class Rules
 */
class Rules
{

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Rates constructor.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resource = $resourceConnection;
    }

    /**
     * @param array $taxRuleIds
     * @param int   $fromId
     * @param int   $limit
     *
     * @return array
     */
    public function getTaxRules(array $taxRuleIds = [], $fromId = 0, $limit = 1000)
    {
        $select = $this->getConnection()->select()
            ->from(
                $this->resource->getTableName('tax_calculation_rule'),
                [
                    'tax_calculation_rule_id',
                    'code',
                    'priority',
                    'position',
                    'calculate_subtotal',
                ]
            );

        if (!empty($taxRuleIds)) {
            $select->where('tax_calculation_rule_id in (?)', $taxRuleIds);
        }

        $select->where('tax_calculation_rule_id > ?', $fromId);
        $select->order('tax_calculation_rule_id');
        $select->limit($limit);

        return $this->getConnection()->fetchAssoc($select);
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
}
