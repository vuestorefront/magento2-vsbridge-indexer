<?php
/**
 * @package  Divante\VsbridgeIndexerCore
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Index\Indicies\Config;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\Data as DataConfig;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class Config
 */
class Data extends DataConfig
{
    const CACHE_ID = 'vsf_indices_config';

    /**
     * Config constructor.
     *
     * @param Reader $reader
     * @param CacheInterface $cache
     * @param string $cacheId
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        Reader $reader,
        CacheInterface $cache,
        string $cacheId = self::CACHE_ID,
        SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
    }
}
