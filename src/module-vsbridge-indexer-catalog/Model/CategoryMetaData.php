<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model;

use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class CategoryMetaData
 */
class CategoryMetaData
{

    /**
     * @var \Magento\Framework\EntityManager\EntityMetadataInterface
     */
    private $categoryMetaData;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * CategoryMetaData constructor.
     *
     * @param MetadataPool $metadataPool
     */
    public function __construct(MetadataPool $metadataPool)
    {
        $this->metadataPool = $metadataPool;
    }

    /**
     * @return \Magento\Framework\EntityManager\EntityMetadataInterface
     * @throws \Exception
     */
    public function get()
    {
        if (null === $this->categoryMetaData) {
            $this->categoryMetaData = $this->metadataPool->getMetadata(
                \Magento\Catalog\Api\Data\CategoryInterface::class
            );
        }

        return $this->categoryMetaData;
    }
}
