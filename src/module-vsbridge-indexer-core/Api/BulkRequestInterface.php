<?php
/**
 * @package   Divante\VsbridgeIndexerCore
 * @author    Agata Firlejczyk <afirlejczyk@divante.pl>
 * @copyright 2019 Divante Sp. z o.o.
 * @license   See LICENSE_DIVANTE.txt for license details.
 */

namespace Divante\VsbridgeIndexerCore\Api;

/**
 * Interface BulkRequestInterface
 */
interface BulkRequestInterface
{
    /**
     * Indicates if the current bulk contains operation.
     *
     * @return boolean
     */
    public function isEmpty();

    /**
     * Return list of operations to be executed as an array.
     *
     * @return array
     */
    public function getOperations();

    /**
     * @param array $data
     *
     * @return array
     */
    public function prepareDocument(array $data);

    /**
     * Add a several documents to the index.
     *
     * $data format have to be an array of all documents with document id as key.
     *
     * @param string $index Index the documents have to be added to.
     * @param string  $type  Document type.
     * @param array          $data  Document data.
     *
     * @return \Divante\VsbridgeIndexerCore\Api\BulkRequestInterface
     */
    public function addDocuments($index, $type, array $data);

    /**
     * @param string $index
     * @param string $type
     * @param array $docIds
     *
     * @return \Divante\VsbridgeIndexerCore\Api\BulkRequestInterface
     */
    public function deleteDocuments($index, $type, array $docIds);
}
