# html5VideoConverter

Simple lib to convert videos using fprobe and ffmpeg.

## Usage

```php
<?php

use Html5VideoConverter\ConfigBuilder;
use Html5VideoConverter\FileInfo;
use Html5VideoConverter\Converter;
use Html5VideoConverter\ProfileBuilder;

$sourceFile = '/full/path/to/original/file/video.mp4';
$destFile = '/full/path/to/dest/file/video.mp4';

$config = ConfigBuilder::build();         // The config is stored in config folder
$profile = ProfileBuilder::build('360p'); // Profiles are stored in profiles folder
$fileInfo = new FileInfo($sourceFile, $config);

$converter = new Converter($config, $fileInfo, $profile);
$converter->execute($destFile);

```
