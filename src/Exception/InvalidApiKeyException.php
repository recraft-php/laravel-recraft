<?php

namespace Recraft\Exception;

use Exception;

class InvalidApiKeyException extends Exception
{
    public function __construct(string $apiKey)
    {
        parent::__construct("the api key [$apiKey] is invalid");
    }
}
