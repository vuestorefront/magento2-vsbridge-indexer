<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Setup;

use Divante\VsbridgeIndexerCatalog\Setup\UpgradeData\SetCategoryDefaultAttributes;
use Divante\VsbridgeIndexerCatalog\Setup\UpgradeData\SetProductDefaultAttributes;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var SetProductDefaultAttributes
     */
    private $setProductDefaultAttributes;

    /**
     * @var SetCategoryDefaultAttributes
     */
    private $setCategoryDefaultAttributes;

    /**
     * UpgradeData constructor.
     *
     * @param SetProductDefaultAttributes $setDefaultAttributes
     * @param SetCategoryDefaultAttributes $setCategoryDefaultAttributes
     */
    public function __construct(
        SetProductDefaultAttributes $setDefaultAttributes,
        SetCategoryDefaultAttributes $setCategoryDefaultAttributes
    ) {
        $this->setProductDefaultAttributes = $setDefaultAttributes;
        $this->setCategoryDefaultAttributes = $setCategoryDefaultAttributes;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->setProductDefaultAttributes->execute();
        }

        if (version_compare($context->getVersion(), '1.3.0', '<')) {
            $this->setCategoryDefaultAttributes->execute();
        }
    }
}
