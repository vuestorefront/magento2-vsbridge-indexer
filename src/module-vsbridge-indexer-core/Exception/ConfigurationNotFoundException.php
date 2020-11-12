<?php

namespace Divante\VsbridgeIndexerCore\Exception;

class ConfigurationNotFoundException extends \LogicException
{
    public function __construct()
    {
        parent::__construct('No configuration found');
    }
}
