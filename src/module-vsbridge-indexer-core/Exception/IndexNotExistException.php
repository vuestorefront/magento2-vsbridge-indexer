<?php

namespace Divante\VsbridgeIndexerCore\Exception;

/**
 * Index does not exist exception
 */
class IndexNotExistException extends \LogicException
{
    /**
     * IndexNotExistException constructor.
     * @param string $indexIdentifier
     */
    public function __construct($indexIdentifier = "")
    {
        parent::__construct($indexIdentifier. " index does not exist yet.");
    }
}
