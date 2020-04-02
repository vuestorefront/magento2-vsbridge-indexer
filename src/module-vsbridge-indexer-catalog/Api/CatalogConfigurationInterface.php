<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Api;

/**
 * Interface CatalogConfigurationInterface
 */
interface CatalogConfigurationInterface
{
    const CATALOG_SETTINGS_XML_PREFIX = 'vsbridge_indexer_settings/catalog_settings';

    /**
     * Slug/url key config
     */
    const USE_MAGENTO_URL_KEYS = 'use_magento_url_keys';
    const USE_URL_KEY_TO_GENERATE_SLUG = 'use_url_key_to_generate_slug';

    /**
     * Prices
     */
    const USE_CATALOG_RULES = 'use_catalog_rules';
    const SYNC_TIER_PRICES = 'sync_tier_prices';

    const ADD_SWATCHES_OPTIONS = 'add_swatches_to_configurable_options';

    /**
     * Allow product types to reindex
     */
    const ALLOWED_PRODUCT_TYPES = 'allowed_product_types';

    /**
     * Product attributes to reindex
     */
    const PRODUCT_ATTRIBUTES = 'product_attributes';
    const CHILD_ATTRIBUTES = 'child_attributes';

    /**
     * Export attributes metadata config field
     */
    const EXPORT_ATTRIBUTES_METADATA = 'export_attributes_metadata';

    /**
     * @return bool
     */
    public function useMagentoUrlKeys();

    /**
     * @return bool
     */
    public function useUrlKeyToGenerateSlug();

    /**
     * @return bool
     */
    public function useCatalogRules();

    /**
     * @return bool
     */
    public function syncTierPrices();

    /**
     * @return bool
     */
    public function canExportAttributesMetadata(): bool;

    /**
     * @return bool
     */
    public function addSwatchesToConfigurableOptions();

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function getAllowedProductTypes($storeId);

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function getAllowedAttributesToIndex(int $storeId): array;

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function getAllowedChildAttributesToIndex(int $storeId): array;

    /**
     *
     * @return array
     * @throws \Exception
     */
    public function getAttributesUsedForSortBy();

    /**
     * @param int $storeId
     *
     * @return string
     */
    public function getProductListDefaultSortBy($storeId);

    /**
     * Retrieve Category Url Suffix
     *
     * @param int $storeId
     *
     * @return string
     */
    public function getCategoryUrlSuffix(int $storeId): string;
}
