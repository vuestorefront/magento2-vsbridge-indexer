<?php
/**
 * @package   magento-2-1.dev
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Index\Indicies\Config;

use Magento\Framework\Config\ConverterInterface;

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
    const TYPE_NODE_TYPE = 'type';

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
        $indexConfig = ['types' => []];
        $typesSearchPath = sprintf('%s', self::TYPE_NODE_TYPE);
        $xpath->query($typesSearchPath, $indexRootNode);

        foreach ($xpath->query($typesSearchPath, $indexRootNode) as $typeNode) {
            $typeParams = $this->parseTypeConfig($xpath, $typeNode);
            $indexConfig['types'][$typeNode->getAttribute('name')] = $typeParams;
        }

        return $indexConfig;
    }

    /**
     * Parse type node configuration.
     *
     * @param \DOMXPath $xpath XPath access to the document parsed.
     * @param \DOMNode $typeRootNode Type node to be parsed.
     *
     * @return array
     */
    private function parseTypeConfig(\DOMXPath $xpath, \DOMNode $typeRootNode)
    {
        $datasources = $this->parseDataProviders($xpath, $typeRootNode);
        $mapping = $typeRootNode->getAttribute('mapping');
        $mappingOptions = [];

        if ($mapping) {
            $mappingOptions[] = $mapping;
        }

        return [
            'mapping' => $mappingOptions,
            'data_providers' => $datasources,
        ];
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
        $datasources = [];

        foreach ($xpath->query(self::DATA_PROVIDERS_PATH, $typeRootNode) as $datasourceNode) {
            $datasources[$datasourceNode->getAttribute('name')] = $datasourceNode->nodeValue;
        }

        return $datasources;
    }
}
