<?php

namespace Divante\VsbridgeIndexerCore\Index;

use Divante\VsbridgeIndexerCore\Api\BulkResponseInterface;

/**
 * Class BulkResponse
 */
class BulkResponse implements BulkResponseInterface
{
    /**
     * @var array
     */
    private $rawResponse;

    /**
     * Constructor.
     *
     * @param array $rawResponse ElasticSearch raw response.
     */
    public function __construct(array $rawResponse)
    {
        $this->rawResponse = $rawResponse;
    }

    /**
     * @inheritDoc
     */
    public function hasErrors()
    {
        return (bool)$this->rawResponse['errors'];
    }

    /**
     * @inheritDoc
     */
    public function getErrorItems()
    {
        return array_filter(
            $this->rawResponse['items'],
            function ($item) {
                return isset(current($item)['error']);
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function getSuccessItems()
    {
        $successes = array_filter(
            $this->rawResponse['items'],
            function ($item) {
                return !isset(current($item)['error']);
            }
        );

        return $successes;
    }

    /**
     * @inheritDoc
     */
    public function aggregateErrorsByReason()
    {
        $errorByReason = [];

        foreach ($this->getErrorItems() as $item) {
            $operationType = current(array_keys($item));
            $itemData = $item[$operationType];
            $index = $itemData['_index'];
            $documentType = $itemData['_type'];
            $errorData = $itemData['error'];
            $errorKey = $operationType . $errorData['type'] . $errorData['reason'] . $index . $documentType;

            if (!isset($errorByReason[$errorKey])) {
                $errorByReason[$errorKey] = $this->prepareErrorByReason($item);
            }

            $errorByReason[$errorKey]['count'] += 1;
            $errorByReason[$errorKey]['document_ids'][] = $itemData['_id'];
        }

        return array_values($errorByReason);
    }

    /**
     * @param array $item
     *
     * @return array
     */
    private function prepareErrorByReason(array $item)
    {
        $operationType = current(array_keys($item));
        $itemData = $item[$operationType];
        $errorData = $itemData['error'];

        return [
            'index' => $itemData['_index'],
            'document_type' => $itemData['_type'],
            'operation' => $operationType,
            'error' => [
                'type' => $errorData['type'],
                'reason' => $errorData['reason'],
            ],
            'count' => 0,
        ];
    }
}
