<?php

namespace Divante\VsbridgeIndexerCatalog\Plugin\Mview;

use Divante\VsbridgeIndexerCatalog\Model\Indexer\ProductProcessor;
use Divante\VsbridgeIndexerCatalog\Api\CatalogConfigurationInterface;
use Magento\Framework\Mview\ViewInterface;

/**
 * Class ViewPlugin
 */
class ViewPlugin
{
    /**
     * @var CatalogConfigurationInterface
     */
    private $catalogSettings;

    /**
     * ViewPlugin constructor.
     *
     * @param CatalogConfigurationInterface $catalogSettings
     */
    public function __construct(CatalogConfigurationInterface $catalogSettings)
    {
        $this->catalogSettings = $catalogSettings;
    }

    /**
     * @param ViewInterface $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetSubscriptions(ViewInterface $subject, array $result): array
    {
        if ($this->catalogSettings->useCatalogRules() && $this->isVsbridgeProductIndexer($subject)) {
            $result['catalogrule_product_price'] = [
                'name' => 'catalogrule_product_price',
                'column' => 'product_id',
                'subscription_model' => null,
            ];
        }

        return $result;
    }

    /**
     * @param ViewInterface $subject
     *
     * @return bool
     */
    private function isVsbridgeProductIndexer(ViewInterface $subject): bool
    {
        return ProductProcessor::INDEXER_ID === $subject->getId();
    }
}
