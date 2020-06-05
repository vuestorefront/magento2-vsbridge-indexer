<?php
/**
 * @package  Divante\VsbridgeIndexerCatalog
 * @author Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license See LICENSE_DIVANTE.txt for license details.
 */

declare(strict_types=1);

namespace Divante\VsbridgeIndexerCatalog\Model\Config\Backend;

use Magento\Framework\App\Config\Value;

/**
 * Class Attributes
 */
class Attributes extends Value
{
    /**
     * @inheritdoc
     */
    public function beforeSave()
    {
        if (is_array($this->getValue())) {
            if (in_array('', $this->getValue())) {
                $this->setValue('');
            }
        }

        return parent::beforeSave();
    }
}
