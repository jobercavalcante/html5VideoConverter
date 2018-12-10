<?php

namespace Html5VideoConverter;

use InvalidArgumentException;
use RuntimeException;

/**
 * Store the media file info
 */
class FileInfo
{
    /**
     *
     * @var Parameters
     */
    private $video;

    /**
     *
     * @var Parameters
     */
    private $audio;

    /**
     * Path to file
     *
     * @var string
     */
    private $fileName;

    /**
     * Video/Audio duration in seconds
     *
     * @var float
     */
    private $duration = 0.0;

    /**
     * Constructor
     *
     * @param string $fileName File path
     * @param Parameters $config Indicate the programs path
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function __construct($fileName, Parameters $config)
    {
        if (!is_string($fileName)) {
            throw new InvalidArgumentException('FileInfo needs the file path');
        }
        if (!file_exists($fileName)) {
            throw new RuntimeException('FileInfo needs be a valid file');
        }
        $this->fileName = $fileName;
        $rawInfo = $this->getRawInfo($fileName, $config);
        $this->loadInfo($rawInfo);
    }

    public function fileName()
    {
        return $this->fileName;
    }

    /**
     * Get the Video Info
     *
     * @return Parameters
     */
    public function video()
    {
        return $this->video;
    }

    /**
     * Get the Audio Info
     *
     * @return Parameters
     */
    public function audio()
    {
        return $this->audio;
    }

    /**
     * Get the time duration in seconds
     *
     * @return float
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Get the file raw info
     *
     * @param string $fileName
     * @param Parameters $config
     * @return string
     */
    private function getRawInfo($fileName, Parameters $config)
    {
        $command = $config->get('ffprobe', 'ffprobe')
            . ' -print_format json -show_streams '
            . $fileName
            . ' 2>&1';

        exec($command, $output);
        return $this->convertRawInfo(join('', $output));
    }

    /**
     * Convert the raw info in array
     *
     * @param string $rawInfoString
     * @return array
     */
    private function convertRawInfo($rawInfoString)
    {
        $jsonString = substr($rawInfoString, strpos($rawInfoString, '"streams": [') + 10, -1);
        return json_decode($jsonString, true);
    }

    /**
     * Convert the info and save.
     *
     * @param array $rawInfo
     */
    private function loadInfo($rawInfo)
    {
        $videoRawInfo = $rawInfo[0];
        $audioRawInfo = $rawInfo[1];
        $videoRawRatio = empty($videoRawInfo['display_aspect_ratio']) ? null : $videoRawInfo['display_aspect_ratio'];
        $videoRatio = $this->checkAspectRatio($videoRawRatio) ? $videoRawRatio : '';

        $this->video = ParametersFactory::factory(array(
            'aspectratio' => !empty($videoRatio) ? $videoRatio : null,
            'width' => !empty($videoRawInfo['width']) ? $videoRawInfo['width'] : null,
            'height' => !empty($videoRawInfo['height']) ? $videoRawInfo['height'] : null,
            'bitrate' => !empty($videoRawInfo['bit_rate']) ? $videoRawInfo['bit_rate'] : null,
        ));

        $this->audio = ParametersFactory::factory(array(
            'samplerate' => !empty($audioRawInfo['sample_rate']) ? $audioRawInfo['sample_rate'] : null,
            'channels' => !empty($audioRawInfo['channels']) ? $audioRawInfo['channels'] : null,
            'bitrate' => !empty($audioRawInfo['bit_rate']) ? $audioRawInfo['bit_rate'] : null,
        ));

        $this->duration = max(
            empty($videoRawInfo['duration']) ? 0.0 : (float) $videoRawInfo['duration'],
            empty($audioRawInfo['duration']) ? 0.0 : (float) $audioRawInfo['duration']
        );
    }

    /**
     * Check if is a valid aspectRatio
     * @param string  $aspectRatio
     * @return boolean
     */
    private function checkAspectRatio($aspectRatio)
    {
        if (empty($aspectRatio)) {
            return false;
        }
        list($width, $height) = explode(':', $aspectRatio);

        return $width != 0 && $height != 0;
    }
}
