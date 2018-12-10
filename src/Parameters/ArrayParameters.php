<?php

namespace Html5VideoConverter\Parameters;

use Html5VideoConverter\Parameters;

/**
 * Config using array
 */
class ArrayParameters extends Parameters
{
    /**
     * Constructor
     *
     * @param array $items
     */
    public function __construct(array $items = array())
    {
        $this->storage = $items;
    }
}
