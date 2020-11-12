<?php

namespace Divante\VsbridgeIndexerCore\Index\Indices\Config;

use Magento\Framework\Config\ConverterInterface;

/**
 * Config DOM-to-array converter interface. Convert configuration from vsbridge.xml to array
 */
class Converter implements ConverterInterface
{
    const ROOT_NODE_NAME = 'config';
    const TYPE_NODE_TYPE = 'type';

    /**
     * @param \DOMDocument $source
     *
     * @return array
     */
    public function convert($source)
    {
        $indices = [];

        $xpath = new \DOMXPath($source);
        $indexSearchPath = sprintf("/%s/%s", self::ROOT_NODE_NAME, self::TYPE_NODE_TYPE);

        foreach ($xpath->query($indexSearchPath) as $indexNode) {
            $indexIdentifier = $indexNode->getAttribute('identifier');
            $indices[$indexIdentifier] = $this->parseTypeConfig($indexNode);
        }

        return $indices;
    }

    /**
     * Parse type node configuration.
     *
     * @param \DOMNode $typeRootNode Type node to be parsed.
     *
     * @return array
     */
    private function parseTypeConfig(\DOMNode $typeRootNode)
    {
        $mapping = $typeRootNode->getAttribute('mapping');

        return ['mapping' => $mapping];
    }
}
