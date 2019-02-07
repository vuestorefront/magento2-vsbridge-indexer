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
    public function getHost();

    /**
     * @return string
     */
    public function getPort();

    /**
     * @return string
     */
    public function getScheme();

    /**
     * @return bool
     */
    public function isHttpAuthEnabled();

    /**
     * @return string
     */
    public function getHttpAuthUser();

    /**
     * @return string
     */
    public function getHttpAuthPassword();

    /**
     * @return array
     */
    public function getOptions();
}
