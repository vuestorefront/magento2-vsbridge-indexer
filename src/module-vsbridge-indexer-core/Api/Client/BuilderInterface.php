<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Api\Client;

/**
 * Interface BuilderInterface
 */
interface BuilderInterface
{
    /**
     * @param array $options
     *
     * @return \Elasticsearch\Client
     */
    public function build(array $options = []);
}
