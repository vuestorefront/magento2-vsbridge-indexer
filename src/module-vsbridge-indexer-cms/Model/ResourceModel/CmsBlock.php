<?php
/**
 * @package   Divante\VsbridgeIndexerCms
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCms\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Store\Model\Store;

/**
 * Class CmsBlock
 */
class CmsBlock
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var MetadataPool
     */
    private $metaDataPool;

    /**
     * Rates constructor.
     *
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection
    ) {
        $this->resource = $resourceConnection;
        $this->metaDataPool = $metadataPool;
    }

    /**
     * @param int $storeId
     * @param array $pageIds
     * @param int   $fromId
     * @param int   $limit
     *
     * @return array
     */
    public function loadBlocks($storeId = 1, array $pageIds = [], $fromId = 0, $limit = 1000)
    {
        $metaData = $this->getCmsBlockMetadata();
        $linkFieldId = $metaData->getLinkField();

        $select = $this->getConnection()->select()->from(['cms_block' => $metaData->getEntityTable()]);
        $select->join(
            ['store_table' => $this->resource->getTableName('cms_block_store')],
            "cms_block.$linkFieldId = store_table.$linkFieldId",
            []
        )->group("cms_block.$linkFieldId");

        $select->where(
            'store_table.store_id IN (?)',
            [
                Store::DEFAULT_STORE_ID,
                $storeId,
            ]
        );

        if (!empty($pageIds)) {
            $select->where('cms_block.block_id IN (?)', $pageIds);
        }

        $select->where('is_active = ?', 1);
        $select->where('cms_block.block_id > ?', $fromId)
            ->limit($limit)
            ->order('cms_block.block_id');

        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }

    /**
     * @return \Magento\Framework\EntityManager\EntityMetadataInterface
     * @throws \Exception
     */
    private function getCmsBlockMetadata()
    {
        return $this->metaDataPool->getMetadata(BlockInterface::class);
    }
}
