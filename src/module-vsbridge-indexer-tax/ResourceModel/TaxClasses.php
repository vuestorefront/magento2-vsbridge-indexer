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
 * Class TaxClasses
 */
class TaxClasses
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
    public function loadTaxClasses(array $ruleIds)
    {
        $select = $this->getConnection()->select();
        $select->from(
            $this->resource->getTableName('tax_calculation'),
            [
                'tax_calculation_rule_id',
                'customer_tax_class_id',
                'product_tax_class_id',
            ]
        )->where('tax_calculation_rule_id IN (?)', $ruleIds);

        $select->distinct(true);

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
}
