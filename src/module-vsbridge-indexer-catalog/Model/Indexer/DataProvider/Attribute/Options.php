<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\Indexer\DataProvider\Attribute;

use Divante\VsbridgeIndexerCore\Api\DataProviderInterface;
use Divante\VsbridgeIndexerCatalog\Model\Attribute\LoadOptions;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as EntityResource;

/**
 * Class Options
 */
class Options implements DataProviderInterface
{

    /**
     * @var LoadOptions
     */
    private $loadOptions;

    /**
     * @var EntityResource
     */
    private $entityAttributeResource;

    /**
     * Options constructor.
     *
     * @param LoadOptions $loadOptions
     * @param EntityResource $entityResource
     */
    public function __construct(
        LoadOptions $loadOptions,
        EntityResource $entityResource
    ) {
        $this->loadOptions = $loadOptions;
        $this->entityAttributeResource = $entityResource;
    }

    /**
     * @inheritdoc
     */
    public function addData(array $indexData, $storeId)
    {
        foreach ($indexData as $attributeId => $attributeData) {
            $attributeData['default_frontend_label'] = $attributeData['frontend_label'];
            $storeLabels = $this->getStoreLabelsByAttributeId($attributeId);

            if (isset($storeLabels[$storeId])) {
                $attributeData['frontend_label'] = $storeLabels[$storeId];
            }

            if ($this->useSource($attributeData)) {
                $attributeData['options'] = $this->getAttributeOptions($attributeData, $storeId);
            }

            $indexData[$attributeId] = $attributeData;
        }

        return $indexData;
    }

    /**
     * @param array $attributeData
     * @param int   $storeId
     *
     * @return array
     */
    public function getAttributeOptions(array $attributeData, $storeId)
    {
        return $this->loadOptions->execute($attributeData['attribute_code'], $storeId);
    }
    
    /**
     * @param array $attributeData
     *
     * @return bool
     */
    private function useSource(array $attributeData)
    {
        return $attributeData['frontend_input'] === 'select' || $attributeData['frontend_input'] === 'multiselect'
               || $attributeData['source_model'] != '';
    }

    /**
     * @param int $attributeId
     *
     * @return array
     */
    private function getStoreLabelsByAttributeId($attributeId)
    {
        return $this->entityAttributeResource->getStoreLabelsByAttributeId($attributeId);
    }
}
