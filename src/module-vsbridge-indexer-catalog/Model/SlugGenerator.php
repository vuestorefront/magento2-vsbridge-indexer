<?php
/**
 * @package   Divante\VsbridgeIndexerCatalog
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCatalog\Model;

use Divante\VsbridgeIndexerCatalog\Api\SlugGeneratorInterface;

/**
 * Class SlugGenerator
 */
class SlugGenerator implements SlugGeneratorInterface
{
    /**
     * @inheritdoc
     */
    public function generate($text, $id)
    {
        $text = $text . '-' . $id;

        return $this->slugify($text);
    }

    /**
     * @param string $text
     *
     * @return string
     */
    private function slugify(string $text)
    {
        $text = mb_strtolower($text);
        $text = preg_replace("/\s+/", '-', $text);// Replace spaces with -
        $text = preg_replace("/&/", '-and-', $text); //Replace & with 'and'
        $text = preg_replace("/[^\w-]+/", '', $text);// Remove all non-word chars
        $text = preg_replace("/--+/", '-', $text);// Replace multiple - with single -

        return $text;
    }
}
