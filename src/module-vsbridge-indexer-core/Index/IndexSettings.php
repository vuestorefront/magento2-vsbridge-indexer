<?php

namespace Divante\VsbridgeIndexerCore\Index;

use Divante\VsbridgeIndexerCore\Index\Indices\Config;
use Divante\VsbridgeIndexerCore\Index\Indices\ConfigParserInterface;
use Divante\VsbridgeIndexerCore\Index\Indices\ConfigResolver;
use Divante\VsbridgeIndexerCore\Config\IndicesSettings;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class IndexSettings
 */
class IndexSettings
{
    const DUMMY_INDEX_IDENTIFIER = 'vue_storefront_catalog';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ConfigResolver
     */
    private $configResolver;

    /**
     * @var IndicesSettings
     */
    private $configuration;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * IndexSettings constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param ConfigResolver $config
     * @param DateTimeFactory $dateTimeFactory
     * @param IndicesSettings $settingsConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ConfigResolver $config,
        IndicesSettings $settingsConfig,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->configResolver = $config;
        $this->configuration = $settingsConfig;
        $this->storeManager = $storeManager;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * Retrieve vsbridge configuration
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->configResolver->resolve();
    }

    /**
     * @return array
     */
    public function getEsConfig()
    {
        return [
            'settings' => [
                'index.mapping.total_fields.limit' => $this->configuration->getFieldsLimit(),
                'analysis' => [
                    'analyzer' => [
                        'autocomplete' => [
                            'tokenizer' => 'autocomplete',
                            'filter' => ['lowercase'],
                        ],
                        'autocomplete_search' => [
                            'tokenizer'=> 'lowercase'
                        ]
                    ],
                    'tokenizer' => [
                        'autocomplete' => [
                            'type' => 'edge_ngram',
                            'min_gram' => 2,
                            'max_gram' => 10,
                            'token_chars' => ['letter'],
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @param string $indexIdentifier
     * @param StoreInterface $store
     *
     * @return string
     */
    public function createIndexName($indexIdentifier, StoreInterface $store)
    {
        $name = $this->getIndexAlias($indexIdentifier, $store);
        $currentDate = $this->dateTimeFactory->create();

        return $name . '_' . $currentDate->getTimestamp();
    }

    /**
     * Create index alias
     *
     * @param string $indexIdentifier
     * @param StoreInterface $store
     *
     * @return string
     */
    public function getIndexAlias(string $indexIdentifier, StoreInterface $store)
    {
        $indexNamePrefix = $this->getIndexNamePrefix();
        $storeIdentifier = $this->getStoreIdentifier($store);

        if ($storeIdentifier) {
            $indexNamePrefix .= '_' . $storeIdentifier;
        }

        $indexNamePrefix .=
            $indexIdentifier === self::DUMMY_INDEX_IDENTIFIER
                ? ''
                : '_' . $indexIdentifier;

        return strtolower($indexNamePrefix);
    }

    /**
     * @param StoreInterface $store
     *
     * @return string
     */
    private function getStoreIdentifier(StoreInterface $store)
    {
        if (!$this->configuration->addIdentifierToDefaultStoreView()) {
            $defaultStoreView = $this->storeManager->getDefaultStoreView();

            if ($defaultStoreView->getId() === $store->getId()) {
                return '';
            }
        }

        return ('code' === $this->getIndexIdentifier()) ? $store->getCode() : (string) $store->getId();
    }

    /**
     * @return string
     */
    private function getIndexNamePrefix()
    {
        return $this->configuration->getIndexNamePrefix();
    }

    /**
     * @return string
     */
    private function getIndexIdentifier()
    {
        return $this->configuration->getIndexIdentifier();
    }

    /**
     * @return int
     */
    public function getBatchIndexingSize()
    {
        return $this->configuration->getBatchIndexingSize();
    }
}
