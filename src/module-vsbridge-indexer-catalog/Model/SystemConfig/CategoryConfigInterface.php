<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.com>
 * @copyright 2020 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model\SystemConfig;

/**
 * @api
 */
interface CategoryConfigInterface
{
    /**
     * @const string
     */
    const CATEGORY_SETTINGS_XML_PREFIX = 'vsbridge_indexer_settings/catalog_category_settings';

    /**
     * Category attributes to reindex
     */
    const CATEGORY_ATTRIBUTES = 'category_attributes';
    const CHILD_ATTRIBUTES = 'child_attributes';

    /**
     * Retrieve Category Url Suffix
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getCategoryUrlSuffix(int $storeId): string;

    /**
     * Retrieve attributes used for sort by
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getAttributesUsedForSortBy(): array;

    /**
     * Retrieve default product attribute used for sort by
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getProductListDefaultSortBy(int $storeId): string;

    /**
     * Retrieve Category Attributes Allowed to export
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getAllowedAttributesToIndex(int $storeId): array;

    /**
     * Retrieve allowed attributes for children categories
     *
     * @param int $storeId
     *
     * @return array
     */
    public function getAllowedChildAttributesToIndex(int $storeId): array;
}
