<?php

namespace Html5VideoConverter;

use Html5VideoConverter\Parameters\ArrayParameters;
use Exception;
use DomainException;

/**
 * Description of Command
 */
class Converter
{
    const NO_PROBLEMS = 0;

    /**
     *
     * @var Parameters $config
     */
    private $config;

    /**
     * @var FileInfo $fileInfo
     */
    private $fileInfo;

    /**
     * @var Profile $profile
     */
    private $profile;

    /**
     * Constructor
     *
     * @param Parameters $config
     * @param FileInfo $fileInfo
     * @param Profile $profile
     */
    public function __construct(Parameters $config = null, FileInfo $fileInfo = null, Profile $profile = null)
    {
        $this->config = $config;
        $this->fileInfo = $fileInfo;
        $this->profile = $profile;
    }

    /**
     * Set the Config
     *
     * @param Parameters $config
     * @return Converter
     */
    public function setConfig(Parameters $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Set the FileInfo
     *
     * @param FileInfo $fileInfo
     * @return Converter
     */
    public function setFileInfo(FileInfo $fileInfo)
    {
        $this->fileInfo = $fileInfo;
        return $this;
    }

    /**
     * Set the Profile
     *
     * @param Profile $profile
     * @return Converter
     */
    public function setProfile(Profile $profile)
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * Execute the converter.
     *
     * @param string $fileName
     * @return bool
     */
    public function execute($fileName)
    {
        $this->prepareExecute();
        
        $output = $return = null;
        $command = $this->makeCommand($fileName);
        
        exec($command, $output, $return);

        return ((int) $return) === self::NO_PROBLEMS;
    }

    /**
     * Verify the items needed to execute the Converter
     *
     * @throws DomainException
     */
    private function prepareExecute()
    {
        if ($this->config === null) {
            throw new DomainException('The Config is needed to execute the Converter');
        }

        if ($this->fileInfo === null) {
            throw new DomainException('The FileInfo is needed to execute the Converter');
        }

        if ($this->profile === null) {
            throw new DomainException('The Profile is needed to execute the Converter');
        }
        set_time_limit(0);
    }

    /**
     * Create the string command
     *
     * @param string $destination File destination
     * @return string
     */
    private function makeCommand($destination)
    {
        $ffmpeg = $this->config->get('ffmpeg', 'ffmpeg');
        $source = $this->fileInfo->fileName();
        $videoOptions = $this->getVideoOptions();
        $audioOptions = $this->getAudioOptions();

        $firstPassTemplate = '%s -y -i %s %s -pass 1 -an %s';
        $secondPassTemplate = '%s -y -i %s -movflags faststart %s -pass 2 %s %s';

        $firstPass = sprintf($firstPassTemplate, $ffmpeg, $source, $videoOptions, $destination);
        $secondPass = sprintf($secondPassTemplate, $ffmpeg, $source, $videoOptions, $audioOptions, $destination);

        return $firstPass . ' && ' . $secondPass . ' 2>&1';
    }

    /**
     * Create the Video options
     *
     * @return string
     */
    private function getVideoOptions()
    {
        $videoProfile = $this->profile->video();

        $options = new ArrayParameters();
        $options->set('-vcodec', $this->config->get('videoCodec', 'libx264'));
        $options->set('-s', $videoProfile->get('width') . 'x' . $videoProfile->get('height'));
        $options->set('-qblur', '7.0');
        $options->set('-r', $videoProfile->get('framerate'));
        $options->set('-b:v', $videoProfile->get('bitrate'));

        $aspectRatio =
            $this->fileInfo->video()->get('aspectratio')
            ?: $this->calculateAspectRatio(
                $this->fileInfo->video()->get('width'),
                $this->fileInfo->video()->get('height')
            );

        $options->set('-aspect', $aspectRatio);

        return (string) $options;
    }

    /**
     * Create the Audio options
     *
     * @return string
     */
    private function getAudioOptions()
    {
        $audioProfile = $this->profile->audio();

        $options = new ArrayParameters();

        // Experimental codec
        $options->set('-acodec', $this->config->get('audioCodec', 'aac'));
        $options->set('-strict', '-2');

        $options->set('-ac', $this->fileInfo->video()->get('channels', 0) ?: 2);
        $options->set('-ar', $audioProfile->get('samplingrate'));
        $options->set('-ab', $audioProfile->get('bitrate'));

        return (string) $options;
    }

    /**
     * Calculate the aspect ratio.
     *
     * @param int $width
     * @param int $height
     * @return string
     */
    private function calculateAspectRatio($width, $height)
    {
        if ($width <= 0 or $height <= 0) {
            return '0:0';
        }

        $total = $width + $height;

        for ($i = 1; $i <= 40; $i++) {
            $widthR  = $i * 1.0 * $width  / $total;
            $heightR = $i * 1.0 * $height / $total;

            if ($i == 40 || (
                    abs($widthR  - round($widthR))  <= 0.02 &&
                    abs($heightR - round($heightR)) <= 0.02
            )) {
                return round($widthR) . ':' . round($heightR);
            }
        }
    }
}
