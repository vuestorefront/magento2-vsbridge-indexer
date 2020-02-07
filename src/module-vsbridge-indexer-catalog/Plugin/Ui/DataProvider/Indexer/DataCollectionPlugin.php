<?php

declare(strict_types=1);

namespace Divante\VsbridgeIndexerCatalog\Plugin\Ui\DataProvider\Indexer;

use Divante\VsbridgeIndexerCatalog\Model\Indexer\ProductCategoryProcessor;
use Magento\Indexer\Ui\DataProvider\Indexer\DataCollection;

/**
 * Class DataCollectionPlugin
 */
class DataCollectionPlugin
{

    /**
     * @param DataCollection $subject
     * @param \Magento\Framework\DataObject[] $items
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function afterGetItems(DataCollection $subject, $items)
    {
        $keyToRemove = $this->findIndexerKey($items);

        if ($keyToRemove) {
            unset($items[$keyToRemove]);
            $subject->removeItemByKey($keyToRemove);
        }

        return $items;
    }

    /**
     * @param \Magento\Framework\DataObject[] $items
     *
     * @return int
     */
    private function findIndexerKey($items): int
    {
        $keyToRemove = - 1;

        foreach ($items as $key => $item) {
            if ($item->getData('indexer_id') === ProductCategoryProcessor::INDEXER_ID) {
                $keyToRemove = $key;
                break;
            }
        }

        return $keyToRemove;
    }
}
