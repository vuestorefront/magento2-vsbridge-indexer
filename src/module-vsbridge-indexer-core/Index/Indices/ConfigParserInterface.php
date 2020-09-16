<?php

namespace Divante\VsbridgeIndexerCore\Index\Indices;

/**
 * @api
 * Interface ConfigParserInterface
 */
interface ConfigParserInterface
{
    /**
     * @param array $array
     * @return array
     */
    public function parse(array $array): array;
}
