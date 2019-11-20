<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types = 1);

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Product\Configurable;

use Divante\VsbridgeIndexerCatalog\Model\Attributes\ConfigurableAttributes;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\AttributeDataProvider;
use Divante\VsbridgeIndexerCatalog\Model\ResourceModel\Product\Prices as PriceResourceModel;
use Divante\VsbridgeIndexerCatalog\Model\TierPriceProcessor;

/**
 * Class ChildAttributesProcessor
 */
class ChildAttributesProcessor
{
    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var TierPriceProcessor
     */
    private $tierPriceProcessor;

    /**
     * @var PriceResourceModel
     */
    private $priceResourceModel;

    /**
     * @var  AttributeDataProvider
     */
    private $resourceAttributeModel;

    /**
     * @var ConfigurableAttributes
     */
    private $configurableAttributes;

    /**
     * ChildAttributesProcessor constructor.
     *
     * @param AttributeDataProvider $attributeDataProvider
     * @param ConfigurableAttributes $configurableAttributes
     * @param TierPriceProcessor $tierPriceProcessor
     * @param PriceResourceModel $priceResourceModel
     * @param int $batchSize
     */
    public function __construct(
        AttributeDataProvider $attributeDataProvider,
        ConfigurableAttributes $configurableAttributes,
        TierPriceProcessor $tierPriceProcessor,
        PriceResourceModel $priceResourceModel,
        $batchSize = 500
    ) {
        $this->batchSize = $batchSize;
        $this->tierPriceProcessor = $tierPriceProcessor;
        $this->priceResourceModel = $priceResourceModel;
        $this->resourceAttributeModel = $attributeDataProvider;
        $this->configurableAttributes = $configurableAttributes;
    }

    /**
     * @param int $storeId
     * @param array $allChildren
     * @param array $configurableAttributeCodes
     *
     * @return array
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadChildrenRawAttributesInBatches($storeId, array $allChildren, array $configurableAttributeCodes)
    {
        $requiredAttributes = array_merge(
            $this->getRequiredChildrenAttributes(),
            $configurableAttributeCodes
        );

        $requiredAttribute = array_unique($requiredAttributes);

        foreach ($this->getChildrenInBatches($allChildren, $this->batchSize) as $batch) {
            $childIds = array_keys($batch);
            $priceData = $this->priceResourceModel->loadPriceData($storeId, $childIds);
            $allAttributesData = $this->resourceAttributeModel->loadAttributesData(
                $storeId,
                $childIds,
                $requiredAttribute
            );

            foreach ($priceData as $childId => $priceDataRow) {
                $allChildren[$childId]['final_price'] = (float)$priceDataRow['final_price'];
                $allChildren[$childId]['regular_price'] = (float)$priceDataRow['price'];
            }

            foreach ($allAttributesData as $childId => $attributes) {
                if ($this->tierPriceProcessor->syncTierPrices()) {
                    /*we need some extra attributes to apply tier prices*/
                    $batch[$childId] = array_merge(
                        $allChildren[$childId],
                        $attributes
                    );
                } else {
                    $allChildren[$childId] = array_merge(
                        $allChildren[$childId],
                        $attributes
                    );
                }
            }

            if ($this->tierPriceProcessor->syncTierPrices()) {
                $batch = $this->tierPriceProcessor->applyTierGroupPrices($batch, $storeId);
                $allChildren = array_replace_recursive($allChildren, $batch);
            }

            $batch = null;
        }

        return $allChildren;
    }

    /**
     * @return array
     */
    private function getRequiredChildrenAttributes(): array
    {
        return $this->configurableAttributes->getChildrenRequiredAttributes();
    }

    /**
     * @param array $documents
     * @param int $batchSize
     *
     * @return \Generator
     */
    private function getChildrenInBatches(array $documents, $batchSize)
    {
        $i = 0;
        $batch = [];

        foreach ($documents as $documentName => $documentValue) {
            $batch[$documentName] = $documentValue;

            if (++$i == $batchSize) {
                yield $batch;
                $i = 0;
                $batch = [];
            }
        }

        if (count($batch) > 0) {
            yield $batch;
        }
    }
}
