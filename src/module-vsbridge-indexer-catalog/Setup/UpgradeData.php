<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Setup;

use Divante\VsbridgeIndexerCatalog\Setup\UpgradeData\SetDefaultAttributes;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var SetDefaultAttributes
     */
    private $setDefaultAttributes;

    /**
     * UpgradeData constructor.
     *
     * @param SetDefaultAttributes $setDefaultAttributes
     */
    public function __construct(SetDefaultAttributes $setDefaultAttributes)
    {
        $this->setDefaultAttributes = $setDefaultAttributes;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->setDefaultAttributes->execute();
        }
    }
}
