<?php

namespace Html5VideoConverter\Parameters;

use Html5VideoConverter\Parameters;
use InvalidArgumentException;

/**
 * Config using JSON
 */
class JSONParameters extends Parameters
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct($stringJSON)
    {
        $this->storage = json_decode($stringJSON, true);

        if ($this->storage === null) {
            throw new InvalidArgumentException('JSON string must be a valid JSON format');
        }
    }
}
