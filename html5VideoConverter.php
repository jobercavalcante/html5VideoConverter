<?php
set_time_limit(0);
define('FFPROBE', "/usr/bin/ffprobe");
define('FFMPEG', "/usr/bin/ffmpeg");
define('PATH', __DIR__);
define('DESTINY', __DIR__."/convertidos");
define('DS', DIRECTORY_SEPARATOR);

class Html5VideoConverter {

    private $defaults = array(
        'profile' => '720p',
        'timelimit' => 0
    );
    private $config;
    private $fileInfo;
    private $videoProfile;

    function __construct($config = array()) {

    }

    function convertVideo($originVideo, $destiny = '', $profile = '') {

        $this->fileInfo = $this->get_file_info($originVideo);
        $this->defaults['profile'] = (!empty($profile) ) ? $profile : $this->defaults['profile'];

        $this->videoProfile = $this->loadProfileInfo();

        $command = $this->makeCommand($originVideo, $destiny);

        echo $command;
        $executed = $this->runCommand($command);

        return ( $executed->status );

    }

    private function makeCommand($originVideo, $destiny) {


        if( ! is_dir( DESTINY ) ){
            mkdir(DESTINY, 0777, true);
            chmod(DESTINY, 0777);
        }

        $videoOptions = $this->loadVideoOptions( );
        $command = FFMPEG . " -y -i " . $originVideo
                    .' '.$videoOptions
                    .' -pass 1'
                    .' -an '.DESTINY.DS.$destiny;
        $command .= ' && ';
        $command .= FFMPEG . " -y -i " . $originVideo
                    .' -movflags faststart'
                    .' '.$videoOptions
                    .' -pass 2'
                    .' '. $this->loadAudioOptions()
                    .' '.DESTINY.DS.$destiny ;
        $command .= ' 2>&1';

        return $command;
    }

    private function loadAudioOptions(){
        $audioOptions['-acodec'] =  'libvo_aacenc';
        $audioOptions['-ac'] = 2;
        $audioOptions['-ar'] = $this->videoProfile['audio']['samplingrate'];
        $audioOptions['-ab'] =  $this->videoProfile['audio']['bitrate'];
        return $this->makeStringOfArrayOptions($audioOptions);
    }

    private function loadVideoOptions(  ) {

        $videoOptions['-s'] = $this->videoProfile['video']['width']
                . 'x'
                . $this->videoProfile['video']['height'];
        $videoOptions['-qblur'] = '7.0';
        $ratio = $this->aspectratio(
                    $this->fileInfo->video->width
                    , $this->fileInfo->video->height
                    );
        $videoOptions['-aspect'] = $ratio['width'].':'.$ratio['height'];
        $videoOptions['-r'] = $this->videoProfile['video']['framerate'];
        $videoOptions['-b'] = $this->videoProfile['video']['bitrate'];
        $videoOptions['-vcodec'] = 'libx264';


        return $this->makeStringOfArrayOptions($videoOptions);

    }

    private function makeStringOfArrayOptions( $array){
        $tempArray = array();
        foreach ($array as $key => $value) {
           $tempArray[] = $key.' '.$value;
        }

        return implode(' ', $tempArray);
    }

    private function aspectratio($a, $b) {
        if ($a <= 0 || $b <= 0) {
            return array(0, 0);
        }
        $total = $a + $b;
        for ($i = 1; $i <= 40; $i++) {
            $arx = $i * 1.0 * $a / $total;
            $brx = $i * 1.0 * $b / $total;
            if ($i == 40 || (
                    abs($arx - round($arx)) <= 0.02 &&
                    abs($brx - round($brx)) <= 0.02 )) {

                return array('width' => round($arx), 'height' => round($brx));
            }
        }
    }

    private function loadProfileInfo() {
        $profileFile =  PATH.DS.'profiles' . DS . $this->defaults['profile'] . '.json';

        return json_decode(
                file_get_contents( $profileFile )
                , true);
    }

    function get_file_info($originalFile) {
        $command = FFPROBE . " -pretty -show_streams $originalFile 2>&1";
        $executed = $this->runCommand($command);
        $commandOutString = $executed->output;
        $fileInfo = new stdClass();

        $searchVideoIndex = array_search('[STREAM]', $commandOutString);
        $searchVideoIndexFinal = array_search('[/STREAM]', $commandOutString);
        $fileInfo->video = $this->makeInfoObject($commandOutString, $searchVideoIndex, $searchVideoIndexFinal);
        unset($commandOutString[$searchVideoIndex], $commandOutString[$searchVideoIndexFinal]);

        $searchAudioIndex = array_search('[STREAM]', $commandOutString);
        $searchAudioIndexFinal = array_search('[/STREAM]', $commandOutString);
        $fileInfo->audio = $this->makeInfoObject($commandOutString, $searchAudioIndex, $searchAudioIndexFinal);

        return $fileInfo;
    }

    private function makeInfoObject($info, $start, $end) {

        $newInfo = array();

        for ($i = $start + 1; $i < $end; ++$i) {
            list($key, $value) = explode('=', $info[$i]);
            $newInfo[$key] = $value;
        }

        return (object) $newInfo;
    }

    private function runCommand($command) {

        $output = null;
        $status = null;

        exec($command, $output, $status);

        $executed = new stdClass();
        $executed->output = $output;
        $executed->status = $status;

        return $executed;
    }

}
