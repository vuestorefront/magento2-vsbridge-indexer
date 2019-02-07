<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Api;

/**
 * Interface BulkResponseInterface
 */
interface BulkResponseInterface
{
    /**
     * @return boolean
     */
    public function hasErrors();

    /**
     * @return array
     */
    public function getErrorItems();

    /**
     * @return array
     */
    public function getSuccessItems();

    /**
     * @return array
     */
    public function aggregateErrorsByReason();
}
