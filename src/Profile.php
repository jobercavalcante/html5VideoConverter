<?php

namespace Html5VideoConverter;

/**
 * Indicates the Video and Audio profiles to convert the video
 */
class Profile
{
    /**
     * Video Profile
     *
     * @var Parameters
     */
    private $video;

    /**
     * Audio Profile
     *
     * @var Parameters
     */
    private $audio;

    /**
     * Constructor
     *
     * @param Parameters $video
     * @param Parameters $audio
     */
    public function __construct(Parameters $video, Parameters $audio)
    {
        $this->video = $video;
        $this->audio = $audio;
    }

    /**
     * Get the Video Profile
     *
     * @return Parameters
     */
    public function video()
    {
        return $this->video;
    }

    /**
     * Get the Audio Profile
     *
     * @return Parameters
     */
    public function audio()
    {
        return $this->audio;
    }
}
