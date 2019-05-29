<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Api\Data;

/**
 * Interface CatalogConfigurationInterface
 */
interface CatalogConfigurationInterface
{
    const CATALOG_SETTINGS_XML_PREFIX = 'vsbridge_indexer_settings/catalog_settings';

    /**
     * @return bool
     */
    public function useMagentoUrlKeys();

    /**
     * @return bool
     */
    public function syncTierPrices();

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function getAllowedProductTypes($storeId);
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
}
