<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Index\Indicies\Config;

use Magento\Framework\Config\ConverterInterface;
use Magento\Framework\View\Asset\NotationResolver\Variable;

/**
 * Class Converter
 */
class Converter implements ConverterInterface
{
    /**
     *
     */
    const ROOT_NODE_NAME = 'indices';

    /**
     *
     */
    const INDEX_NODE_TYPE = 'index';

    /**
     *
     */
    const DATA_PROVIDERS_NODE_TYPE = 'data_providers';

    /**
     *
     */
    const DATA_PROVIDERS_PATH = 'data_providers/data_provider';

    /**
     * @param \DOMDocument $source
     *
     * @return array
     */
    public function convert($source)
    {
        $indices = [];

        $xpath = new \DOMXPath($source);
        $indexSearchPath = sprintf("/%s/%s", self::ROOT_NODE_NAME, self::INDEX_NODE_TYPE);

        foreach ($xpath->query($indexSearchPath) as $indexNode) {
            $indexIdentifier = $indexNode->getAttribute('identifier');
            $indices[$indexIdentifier] = $this->parseIndexConfig($xpath, $indexNode);
        }

        return $indices;
    }

    /**
     * Parse index node configuration.
     *
     * @param \DOMXPath $xpath XPath access to the document parsed.
     * @param \DOMNode $indexRootNode Index node to be parsed.
     *
     * @return array
     */
    private function parseIndexConfig(\DOMXPath $xpath, \DOMNode $indexRootNode)
    {
        $datasources = $this->parseDataProviders($xpath, $indexRootNode);
        $mapping = $this->parseMapping($xpath, $indexRootNode);

        $indexConfig = [
            'data_providers' => $datasources,
            'mapping' => $mapping,
        ];

        return $indexConfig;
    }

    private function parseMapping(\DOMXPath $xpath, \DOMNode $typeRootNode)
    {
        $mapping = (string)$typeRootNode->getAttribute('mapping');

        return $mapping;
    }

    /**
     * Parse dataprovides from type node configuration.
     *
     * @param \DOMXPath $xpath XPath access to the document parsed.
     * @param \DOMNode $typeRootNode Type node to be parsed.
     *
     * @return array
     */
    private function parseDataProviders(\DOMXPath $xpath, \DOMNode $typeRootNode)
    {
        $dataProviders = [];

        foreach ($xpath->query(self::DATA_PROVIDERS_PATH, $typeRootNode) as $dataProviderNode) {
            $dataProviders[$dataProviderNode->getAttribute('name')] = $dataProviderNode->nodeValue;
        }

        return $dataProviders;
    }
}
