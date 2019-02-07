<?php

namespace Divante\VsbridgeIndexerCore\Index;

use Divante\VsbridgeIndexerCore\Index\Indicies\Config as IndicesConfig;
use Divante\VsbridgeIndexerCore\Config\IndicesSettings;

/**
 * Class IndexSettings
 */
class IndexSettings
{

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
     * @param IndicesConfig $config
     * @param IndicesSettings $settingsConfig
     */
    public function __construct(
        IndicesConfig $config,
        IndicesSettings $settingsConfig
    ) {
        $this->indicesConfig = $config;
        $this->settingConfig = $settingsConfig;
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
     * @return string
     */
    public function getIndexNamePrefix()
    {
        return $this->settingConfig->getIndexNamePrefix();
    }

    /**
     * @return int
     */
    public function getBatchIndexingSize()
    {
        return $this->settingConfig->getBatchIndexingSize();
    }
}
