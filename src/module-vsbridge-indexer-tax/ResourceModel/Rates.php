<?php
/**
 * @package   magento-2-1.dev
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerTax\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Class Rates
 */
class Rates
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
     * @param array $ruleIds
     *
     * @return array
     */
    public function loadTaxRates(array $ruleIds)
    {
        $select = $this->getConnection()->select();
        $select->from(
            ['calculation' => $this->resource->getTableName('tax_calculation')],
            ['calculation.tax_calculation_rule_id']
        )
            ->join(
                ['rate' => $this->resource->getTableName('tax_calculation_rate')],
                'rate.tax_calculation_rate_id = calculation.tax_calculation_rate_id'
            )
            ->where('tax_calculation_rule_id IN (?)', $ruleIds);
        $select->distinct(true);

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
