<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Api\Mapping;

/**
 * Interface FieldInterface
 */
interface FieldInterface
{
    const TYPE_KEYWORD = 'keyword';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_DOUBLE = 'double';
    const TYPE_INTEGER = 'integer';
    const TYPE_LONG = 'long';
    const TYPE_TEXT = 'text';
    const TYPE_DATE = 'date';

    const DATE_FORMAT = 'yyyy-MM-dd HH:mm:ss||yyyy-MM-dd||epoch_millis';
}
