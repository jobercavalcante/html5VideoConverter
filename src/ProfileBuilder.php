<?php

namespace Html5VideoConverter;

class ProfileBuilder
{
    public static function build($fileName)
    {
        if (!file_exists($fileName)) {
            $fileName = __DIR__ . '/../profiles/' . $fileName . '.json';
        }

        $config = ParametersFactory::factory($fileName);

        return new Profile(
            ParametersFactory::factory($config->get('video')),
            ParametersFactory::factory($config->get('audio'))
        );
    }
}
