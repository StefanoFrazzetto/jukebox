<?php

require_once __DIR__ . "/Config.php";
require_once __DIR__ . "/StringUtils.php";
require_once __DIR__ . "/Exception/UploadException.php";
require_once __DIR__ . "/Cover.php";

/**
 * Class Uploader is used to handle the upload of albums into the jukebox.
 */
class Uploader
{
    /** @const string The status file name */
    const STATUS_FILE = "uploader_status.json";
    /** @const array The array of allowed music extensions */
    const ALLOWED_MUSIC_EXTENSIONS = ['mp3'];
    /** @var string The uploader temp directory */
    private $tmp_path;

    public function __construct()
    {
        $this->tmp_path = self::getPath();
    }

    /**
     * @return string the path to the temp folder
     */
    private static function getPath()
    {
        $config = new Config();
        return $config->get("paths")["uploader"];
    }

    /**
     * @return string JSON that indicates the status of the upload.
     */
    public static function getStatus()
    {
        $config_path = self::getPath();
        return json_encode($config_path . self::STATUS_FILE);
    }

    /**
     * Sets the current status in the status file.
     *
     * @param string $json The JSON file to be saved
     */
    public static function setStatus($json)
    {
        if (empty($json)) {
            throw new InvalidArgumentException("The parameter must not be empty.");
        }

        $status_file = self::getPath() . self::STATUS_FILE;
        if (!file_exists($status_file)) {
            file_put_contents($status_file, '');
        }


    }

    /**
     * Uploads a file into the specified directory.
     *
     * @param string $uploadFolderID The destination directory
     * @return bool true if the operation succeeds, false otherwise.
     * @throws UploadException if the file was not uploaded or if the
     * extension of the file is not allowed.
     */
    public static function upload($uploadFolderID)
    {
        if (empty($uploadFolderID)) {
            throw new InvalidArgumentException("The upload folder ID must not be empty.");
        }

        if (!isset($_FILES['file'])) {
            throw new UploadException(UPLOAD_ERR_NO_FILE);
        }

        // Check if the file was uploaded
        if ($_FILES['file']['error'] != 0) {
            throw new UploadException($_FILES['file']['error']);
        }

        $file_extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $file_extension = strtolower($file_extension);

        // Check allowed extensions
        $allowed_extensions = array_merge(Cover::ALLOWED_COVER_EXTENSIONS, self::ALLOWED_MUSIC_EXTENSIONS);
        if (!in_array($file_extension, $allowed_extensions)) {
            throw new UploadException(UPLOAD_ERR_EXTENSION);
        }

        $file_name = StringUtils::cleanString($_FILES['file']['name']);
        $source_file = $_FILES['file']['tmp_name'];

        // Check if the destination directory exists
        $destination_path = self::getPath() . $uploadFolderID;
        if (!is_dir($destination_path)) {
            mkdir($destination_path, 0777, true);
        }

        $destination_file = $destination_path . '/' . $file_name;

        return move_uploaded_file($source_file, $destination_file);
    }

    public static function createStatus($status, $message = "")
    {
        $return['status'] = $status;

        if ($status == "error") {
            if (empty($message)) {
                $message = 'Unknown error.';
            }

            $return['error'] = $message;
            http_response_code(400);
        }

        return json_encode($return);
    }

    public function getID3($folder)
    {
        // TODO
    }
}