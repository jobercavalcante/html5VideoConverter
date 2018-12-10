<?php

namespace Html5VideoConverter;

use Html5VideoConverter\Parameters\ArrayParameters;
use Html5VideoConverter\Parameters\JSONParameters;
use InvalidArgumentException;
use RuntimeException;

/**
 * Create the Config based on the paramater
 */
class ParametersFactory
{
    /**
     * Load the Config
     *
     * @param string|array $parameter
     * @return Config
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public static function factory($parameter)
    {
        if (is_array($parameter)) {
            return new ArrayParameters($parameter);
        }

        if (!is_string($parameter) or empty($parameter)) {
            throw new InvalidArgumentException('Parameter is not valid');
        }

        if (!file_exists($parameter)) {
            throw new RuntimeException('Parameters needs be a valid file');
        }

        // trying array
        if (strpos($parameter, '.php') !== false) {
            $items = include $parameter;

            if (is_array($items)) {
                return new ArrayParameters($items);
            }
        }

        return new JSONParameters(file_get_contents($parameter));
    }
}
