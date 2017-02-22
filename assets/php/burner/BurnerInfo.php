<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'variables.php';
require_once 'autoload.php';

class BurnerInfo
{
    protected static $_burner_folder = '/var/www/html/jukebox/burner';
    protected static $_burner_status = '/tmp/burner_status.json';
    protected static $_burner_message = '/tmp/burner_message.json';
    protected static $_burn_output = '/tmp/burn.log';
    protected static $_decode_output = '/tmp/decode.log';

    protected static $_status_idle = 'Idle';
    protected static $_status_decoding = 'Decoding';
    protected static $_status_iso = 'Creating ISO';
    protected static $_status_normalizing = 'Normalizing';
    protected static $_status_burning = 'Burning';
    protected static $_status_complete = 'Complete';
    protected static $_steps = 5;
    protected $output_format;
    protected $status;
    protected $message;

    // If TRUE, asks the user to put a blank CD.
    protected $nextCD;

    // Percentage for the progress bar.
    protected $percentage;
    protected $partial_progress;

    /**
     * BurnerInfo constructor.
     *
     * @param string $request
     * @param string $output_format
     */
    public function __construct($request = '', $output_format = '')
    {
        $this->setStatus();
        $this->output_format = $output_format;
        $this->setMessage();
        $this->nextCD;
        $this->partial_progress = 0;

        $output = ['status' => $this->getStatus(), 'message' => $this->getMessage(), 'nextCD' => $this->nextCD, 'percentage' => $this->percentage];

        if ($request == '') {
            echo json_encode($output);
        } else {
            return $output;
        }

        return 0;
    }

    /* ********************************************* */

    public function getStatus()
    {
        return $this->status;
    }

    protected function setStatus()
    {
        if (!(file_exists(self::$_burner_status))) {
            $this->status = self::$_status_idle;
        } else {
            $content = file_get_contents(self::$_burner_status);
            $json = json_decode($content, true);
            $this->status = $json['status'];
        }
    }

    /* ********************************************* */

    public function getMessage()
    {
        return $this->message;
    }

    protected function setMessage()
    {

//		$message = "";
        $nextCD = false;
        self::setPercentage(0);

        switch ($this->getStatus()) {
            case self::$_status_idle:
                $message = 'Ready...';
                break;

            case self::$_status_decoding:
                $message = 'Track: '.$this->decoding();

                self::setPercentage(10);
                break;

            case self::$_status_normalizing:
                $message = 'Normalizing tracks...';

                if ($this->output_format == 'wav') {
                    self::setPercentage(50);
                } else {
                    self::setPercentage(34);
                }
                break;

            case self::$_status_iso:
                $message = 'Creating the ISO image...';
                self::setPercentage(50);
                break;

            case self::$_status_burning:
                $message = 'The content is being burned.';
                $this->burning();
                self::setPercentage(65);
                break;

            case self::$_status_complete:
                $message = 'Your disc is ready!';
                self::setPercentage(100);
                unlink(self::$_burner_status);
                // Check if there's something else to burn.
                $tracks = TracksHandler::getTracksJSON();
                if ($tracks !== null && count($tracks) > 0) {
                    $nextCD = true;
                    $message = 'Ready. Insert the NEXT DISC.';
                }
                break;

            default:
                $message = 'Error. File: BurnerInfo.php';
        }

        $this->message = $message;
        $this->nextCD = $nextCD;
    }

    /**
     * $partial_progress = tracks processed / total tracks.
     *
     * @param $base_percentage
     */
    protected function setPercentage($base_percentage)
    {
        $progress = $this->partial_progress * (100 / self::$_steps);

        $final = $base_percentage + floor($progress);

        if ($final > 100) {
            $final = 100;
        }

        $this->percentage = $final;
    }

    /* ********************************************* */

    protected function decoding()
    {
        $partial = FileUtil::countFiles(BurnerHandler::$_burner_folder, 'wav');
        $total = FileUtil::countFiles(BurnerHandler::$_burner_folder, 'mp3');

        $this->partial_progress = $this->percentage($partial, $total);

        $handle = fopen(self::$_decode_output, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (strpos($line, 'input:')) {
                    break;
                }
            }

            $line = str_replace("\t", '', $line); // remove tabs
            $line = str_replace("\n", '', $line); // remove new lines
            $line = str_replace("\r", '', $line); // remove carriage returns
            $line = basename($line);
        } else {
            $line = 'Undefined error.';
        }

        fclose($handle);

        return $line;
    }

    protected function normalizing()
    {
        if (!(file_exists(self::$_burner_message))) {
            $this->partial_progress = 0;
        }

        $content = file_get_contents(self::$_burner_message);
        $json = json_decode($content, true);

        if ($content == null or ($json == null or $json == false) or ($json['partial'] == 0 or $json['total'] == 0)) {
            $this->partial_progress = 0;
        }

        $this->partial_progress = $this->percentage($json['partial'], $json['total']);
    }

    protected function percentage($par, $tot)
    {
        if ($tot == 0 || $tot == null) {
            return 0;
        }

        return $par / $tot;
    }

    /* ********************************************* */

    protected function burning()
    {
        $file_content = file_get_contents(self::$_burn_output);

        $tracks_count = substr_count($file_content, 'Track');
        $total = FileUtil::countFiles(BurnerHandler::$_burner_folder, 'mp3');

        $this->partial_progress = $this->percentage($tracks_count, $total);
    }

    protected function checkProcesses()
    {
        if (CommandExecutor::isProcessRunning('lame')) {
            $output['status'] = self::$_status_decoding;
        } elseif (CommandExecutor::isProcessRunning('mkisofs') || CommandExecutor::isProcessRunning('genisoimage')) {
            $output['status'] = self::$_status_iso;
        } elseif (CommandExecutor::isProcessRunning('normalize-audio')) {
            $output['status'] = self::$_status_normalizing;
        } elseif (CommandExecutor::isProcessRunning('wodim')) {
            $output['status'] = self::$_status_burning;

            $output['message'] = 'Please wait. Your DISC will be ejected once the process will be complete.';
        } else {
            // Not doing anything...perhaps is it still copying the files?
            $output['status'] = self::$_status_idle;
        }

        return $output;
    }
}

$BurnerInfo = new BurnerInfo();
