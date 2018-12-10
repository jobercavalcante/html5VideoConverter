<?php

namespace Html5VideoConverter;

/**
 * Load the Config file
 */
class ConfigBuilder
{
    /**
     * Create the config.
     * @param string $fileName Empty load the default config
     * @return Parameters
     */
    public static function build($fileName = '')
    {
        if (empty($fileName)) {
            $fileName = 'config';
        }
        if (!file_exists($fileName) or is_dir($fileName)) {
            $fileName = __DIR__ . '/../config/' . $fileName . '.php';
        }

        return ParametersFactory::factory($fileName);
    }
}
