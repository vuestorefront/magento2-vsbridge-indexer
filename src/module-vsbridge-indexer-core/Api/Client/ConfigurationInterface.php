<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Api\Client;

/**
 * Interface ConfigurationInterface
 */
interface ConfigurationInterface
{
    /**
     * @return string
     */
    public function getHost(int $storeId);

    /**
     * @return string
     */
    public function getPort(int $storeId);

    /**
     * @return string
     */
    public function getScheme(int $storeId);

    /**
     * @return bool
     */
    public function isHttpAuthEnabled(int $storeId);

    /**
     * @return string
     */
    public function getHttpAuthUser(int $storeId);

    /**
     * @return string
     */
    public function getHttpAuthPassword(int $storeId);

    /**
     * @return array
     */
    public function getOptions(int $storeId);
}
