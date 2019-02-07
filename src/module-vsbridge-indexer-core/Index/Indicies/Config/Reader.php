<?php
/**
 * @package   magento-2-1.dev
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Index\Indicies\Config;

use Magento\Framework\Config\Dom;
use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Config\ValidationStateInterface;

/**
 * Class Reader
 */
class Reader extends Filesystem
{

    const FILE_NAME = 'vsbridge_indices.xml';

    /**
     * List of attributes by XPath used as ids during the file merge process.
     *
     * @var array
     */
    private $idAttributes = [
        '/indices/index' => 'identifier',
        '/indices/index/type' => 'name',
        '/indices/index/type/data_providers/data_provider' => 'name',
    ];

    /**
     * Reader constructor.
     *
     * @param \Magento\Framework\Config\FileResolverInterface $fileResolver
     * @param Converter $converter
     * @param SchemaLocator $schemaLocator
     * @param \Magento\Framework\Config\ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        FileResolverInterface $fileResolver,
        Converter $converter,
        SchemaLocator $schemaLocator,
        ValidationStateInterface $validationState,
        string $fileName = self::FILE_NAME,
        array $idAttributes = [],
        string $domDocumentClass = Dom::class,
        string $defaultScope = 'global'
    ) {
        $idAttributes = $this->idAttributes;

        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
    }
}
