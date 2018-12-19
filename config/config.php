<?php

return array(
    'ffprobe'    => '/usr/bin/ffprobe',
    'ffmpeg'     => 'nice -n 15 /usr/bin/ffmpeg', // Using nice to not overload the system
    'videoCodec' => 'libx264',
    'audioCodec' => 'aac',
);
