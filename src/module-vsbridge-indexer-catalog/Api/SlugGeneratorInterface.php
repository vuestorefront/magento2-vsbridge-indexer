<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Api;

/**
 * Interface SlugGeneratorInterface
 */
interface SlugGeneratorInterface
{

    /**
     * @param string $name
     * @param int $id
     *
     * @return string
     */
    public function generate($name, $id);
}
