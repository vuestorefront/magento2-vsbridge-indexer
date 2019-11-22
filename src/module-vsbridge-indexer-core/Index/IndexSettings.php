<?php

namespace Divante\VsbridgeIndexerCore\Index;

use Divante\VsbridgeIndexerCore\Index\Indicies\Config as IndicesConfig;
use Divante\VsbridgeIndexerCore\Config\IndicesSettings;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class IndexSettings
 */
class IndexSettings
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var IndicesConfig
     */
    private $indicesConfig;

    /**
     * @var IndicesSettings
     */
    private $settingConfig;

    /**
     * IndexSettings constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param IndicesConfig $config
     * @param IndicesSettings $settingsConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        IndicesConfig $config,
        IndicesSettings $settingsConfig
    ) {
        $this->indicesConfig = $config;
        $this->settingConfig = $settingsConfig;
        $this->storeManagerInterface = $storeManager;
    }

    /**
     * @return array
     */
    public function getIndicesConfig()
    {
        return $this->indicesConfig->get();
    }

    /**
     * @return array
     */
    public function getEsConfig()
    {
        return [
            'index.mapping.total_fields.limit' => $this->settingConfig->getFieldsLimit(),
            'analysis' => [
                'analyzer' => [
                    'autocomplete' => [
                        'tokenizer' => 'autocomplete',
                        'filter' => ['lowercase']
                    ],
                    'autocomplete_search' => [
                        'tokenizer'=> 'lowercase'
                    ]
                ],
                'tokenizer'=> [
                    'autocomplete' => [
                        'type'=> 'edge_ngram',
                        'min_gram'=> 2,
                        'max_gram'=> 10,
                        'token_chars'=> ['letter']
                    ]
                ]
            ]
        ];
    }

    /**
     * @param StoreInterface $store
     *
     * @return string
     */
    public function createIndexName(StoreInterface $store)
    {
        $name = $this->getIndexAlias($store);
        $currentDate = new \DateTime();

        return $name . '_' . $currentDate->getTimestamp();
    }

    /**
     * @param StoreInterface $store
     *
     * @return string
     */
    public function getIndexAlias(StoreInterface $store)
    {
        $indexNamePrefix = $this->getIndexNamePrefix();
        $storeIdentifier = $this->getStoreIdentifier($store);

        if ($storeIdentifier) {
            $indexNamePrefix .= '_' . $storeIdentifier;
        }

        return $indexNamePrefix;
    }

    /**
     * @param StoreInterface $store
     *
     * @return string
     */
    private function getStoreIdentifier(StoreInterface $store)
    {
        if (!$this->settingConfig->addIdentifierToDefaultStoreView()) {
            $defaultStoreView = $this->storeManagerInterface->getDefaultStoreView();

            if ($defaultStoreView->getId() === $store->getId()) {
                return '';
            }
        }

        return ('code' === $this->getIndexIdentifier()) ? $store->getCode() : (string) $store->getId();
    }

    /**
     * @return string
     */
    public function getIndexNamePrefix()
    {
        return $this->settingConfig->getIndexNamePrefix();
    }

    /**
     * @return string
     */
    public function getIndexIdentifier()
    {
        return $this->settingConfig->getIndexIdentifier();
    }

    /**
     * @return int
     */
    public function getBatchIndexingSize()
    {
        return $this->settingConfig->getBatchIndexingSize();
    }
}
