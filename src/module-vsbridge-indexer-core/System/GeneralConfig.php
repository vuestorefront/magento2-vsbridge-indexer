<?php declare(strict_types=1);

/**
 * @package  Divante\VsbridgeIndexerCore
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\System;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @inheritdoc
 */
class GeneralConfig implements GeneralConfigInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ClientConfiguration constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function canReindexStore($storeId): bool
    {
        $allowedStores = $this->getStoresToIndex();

        if (in_array($storeId, $allowedStores)) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getStoresToIndex(): array
    {
        $stores = $this->scopeConfig->getValue(self::XML_PATH_ALLOWED_STORES_TO_REINDEX);

        if (null === $stores || '' === $stores) {
            $stores = [];
        } else {
            $stores = explode(',', $stores);
        }

        return $stores;
    }

    /**
     * @inheritdoc
     */
    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(self::XML_PATH_GENERAL_INDEXER_ENABLED);
    }
}
