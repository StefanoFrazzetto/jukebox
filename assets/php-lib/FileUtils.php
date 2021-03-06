<?php

namespace Lib;

use Exception;
use FilesystemIterator;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * FileUtils provide static methods to access, create, modify, and delete
 * files and directories.
 *
 * @author Stefano Frazzetto <https://github.com/StefanoFrazzetto>
 */
abstract class FileUtils
{
    /**
     * Create a file.
     *
     * @param string $file_path The file path
     * @param mixed  $content   The content to be written
     * @param bool   $append    If set to true, appends the content rather than deleting the
     *                          previous file content
     * @param bool   $json      If set to true, the content is encoded to json format
     *
     * @return bool true if the operation succeeds, false otherwise.
     */
    public static function createFile($file_path, $content = '', $append = false, $json = false)
    {
        if (empty($file_path)) {
            throw new InvalidArgumentException('The file path cannot be empty.');
        }

        if ($json) {
            $content = json_encode($content);
        }

        if ($append) {
            $res = file_put_contents($file_path, $content, FILE_APPEND);
        } else {
            $res = file_put_contents($file_path, $content);
        }

        return $res !== false;
    }

    /**
     * Writes the json representation of an array into a file.
     *
     * @param array  $content   the content to write.
     * @param string $file_path the file path.
     *
     * @return bool true on success, false on failure.
     */
    public static function writeJson($content, $file_path)
    {
        if (empty($file_path)) {
            throw new InvalidArgumentException('You must specify a valid path.');
        }

        if (!is_array($content)) {
            throw new InvalidArgumentException('The content must be an array.');
        }

        $json_content = [];
        if (file_exists($file_path)) {
            $file_contents = file_get_contents($file_path);
            $json_content = json_decode($file_contents, true);
        }

        foreach ($content as $key => $value) {
            $json_content[$key] = $value;
        }

        $data = json_encode($json_content);

        return file_put_contents($file_path, $data) !== false;
    }

    /**
     * Return true if a directory is empty.
     *
     * @param string $dir The directory to check
     *
     * @throws InvalidArgumentException if the directory does not exist.
     *
     * @return bool true if the directory is empty, false otherwise.
     */
    public static function isDirEmpty($dir)
    {
        if (!file_exists($dir)) {
            throw new InvalidArgumentException('The directory does not exist');
        }

        if (count(glob($dir.'/*', GLOB_NOSORT)) === 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Removes an index from a json file.
     *
     * @param string $json_file the path to the json file.
     * @param string $index     the index to remove.
     *
     * @throws Exception if the json file does not exist.
     *
     * @return bool true on success, false otherwise.
     */
    public static function removeIndexFromJson($json_file, $index)
    {
        if (!file_exists($json_file)) {
            throw new Exception('The json file does not exist');
        }

        $file = file_get_contents($json_file);
        $json = json_decode($file, true);

        if (empty($json[$index])) {
            return false;
        }

        unset($json[$index]);

        return file_put_contents($json_file, json_encode($json));
    }

    /**
     * Removes the content of the directory recursively.
     *
     * @param string $dir_path the directory to empty
     *
     * @return bool true if it was possible to empty the directory, false
     *              otherwise.
     */
    public static function emptyDirectory($dir_path)
    {
        OS::execute("find $dir_path -name ._\\* -print0 | xargs -0 rm -f");
        OS::execute("rm -rf $dir_path/*");

        return self::countFiles($dir_path) === 0;
    }

    /**
     * Count files in a directory.
     * If a file extension is passed as second parameter, only the files
     * with that extension will be counted.
     *
     * @param string $directory The directory where the files should be counted.
     * @param string $ext       The optional extension of the files.
     *
     * @return int The number of files.
     */
    public static function countFiles($directory, $ext = '')
    {
        $directory = rtrim($directory, '/');

        if (empty($ext)) {
            $ext = '*';
        }

        return count(glob($directory."/*.$ext", GLOB_NOSORT));
    }

    /**
     * Move (rename) file(s).
     *
     * @param string $source      The source file or directory to move.
     * @param string $destination The destination file or directory.
     * @param bool   $force       If set to true, do not prompt before overriding.
     *                            The default value is false.
     *
     * @return bool True if the operation is successful, false otherwise.
     */
    public static function move($source, $destination, $force = false)
    {
        $cmd = 'mv ';

        // Use the force.
        if ($force) {
            $cmd .= '-f';
        }

        $args = "$source $destination";

        return OS::executeWithResult($cmd, $args);
    }

    /**
     * Moves the content of one folder to another one file(s).
     *
     * @param string $source      The source directory to move.
     * @param string $destination The destination directory.
     * @param bool   $force       If set to true, do not prompt before overriding.
     *                            The default value is false.
     *
     * @return bool True if the operation is successful, false otherwise.
     */
    public static function moveContents($source, $destination, $force = false)
    {
        $flags = '-v';

        if ($force) {
            $flags .= ' -f';
        }

        $cmd = "mv $flags $source* $destination";

        return OS::executeWithResult($cmd);
    }

    /**
     * Copy SOURCE to DEST, or multiple SOURCE(s) to DIRECTORY.
     *
     * @param string $source      The source file or directory.
     * @param string $destination The destination file or directory.
     * @param bool   $force       If an existing destination file cannot be opened,
     *                            remove it and try again.
     *
     * @return bool True if the operation is successful, false otherwise.
     */
    public static function copy($source, $destination, $force = false)
    {
        $cmd = 'cp ';

        if ($force) {
            $cmd .= '-f';
        }

        $args = "$source $destination";

        return OS::executeWithResult($cmd, $args);
    }

    /**
     * Remove files that match an extension.
     *
     * @param string $path      The directory where the file(s) are located.
     * @param string $ext       The file(s) extension.
     * @param bool   $recursive If set to true, remove the files recursively.
     *
     * @return bool true if the operation was successful, false otherwise.
     */
    public static function removeByExtension($path, $ext, $recursive = false)
    {
        $path = "$path/*.$ext";

        return self::remove($path, $recursive);
    }

    /**
     * Remove files or directories.
     *
     * @param string $path      The file or directory path.
     * @param bool   $recursive If set to true, remove directories and their contents
     *                          recursively.
     *
     * @return bool true if the operation was successful, false otherwise.
     */
    public static function remove($path, $recursive = false)
    {
        $cmd = 'rm -f ';

        if ($recursive) {
            $cmd .= '-r';
        }

        return OS::executeWithResult($cmd, $path);
    }

    public static function getDirectories($path)
    {
        if (empty($path)) {
            throw new InvalidArgumentException('You must pass a valid path.');
        }

        if (!file_exists($path)) {
            return [];
        }

        $iterator = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
        $dirs = [];

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                $dirs[] = $item->getFilename();
            }
        }

        return $dirs;
    }

    /**
     * Return the size in KB of the directory.
     *
     * @param string $path The directory path.
     *
     * @throws Exception if the directory does not exist.
     *
     * @return int $size The size of the directory in KB.
     */
    public static function getDirectorySize($path)
    {
        if (!file_exists($path)) {
            throw new Exception("The directory at $path does not exist.");
        }

        $bytes = 0;
        if ($path !== false && $path != '' && file_exists($path)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object) {
                $bytes += $object->getSize();
            }
        }

        return $bytes / 1024;
    }

    /**
     * Return the size in KB of the file.
     *
     * @param string $path The file path.
     *
     * @throws Exception if the file does not exist.
     *
     * @return int $size The size of the file in KB.
     */
    public static function getFileSize($path)
    {
        if (!file_exists($path)) {
            throw new Exception("The file at $path does not exist.");
        }

        $bytes = filesize($path);

        return $bytes / 1024;
    }

    /**
     * Return the track length in seconds.
     *
     * If the track does not exists, 0 is returned.
     *
     * @param string $track_path The track path.
     *
     * @return int The track length in seconds.
     */
    public static function getTrackLength($track_path)
    {
        if (StringUtils::contains($track_path, '.mp3')) {
            $cmd = "mp3info -p '%S' $track_path | grep -o '[0-9]*'";
        } else {
            $cmd = "soxi -D $track_path | grep -o '[0-9.]*'";
        }

        return intval(OS::execute($cmd));
    }

    /**
     * Remove all files in the directory that are older than the
     * specified time in seconds.
     *
     * @param string $dir     The directory containing the files to remove.
     * @param int    $seconds the time in seconds.
     *
     * @throws InvalidArgumentException if the directory does not exist or
     *                                  if the time in seconds is less than 0.
     */
    public static function deleteFilesOlderThan($dir, $seconds)
    {
        if (!file_exists($dir)) {
            throw new InvalidArgumentException('The specified directory does not exist.');
        }

        if (!is_int($seconds) || $seconds < 0) {
            throw new InvalidArgumentException('The second argument must be a positive integer.');
        }

        foreach (glob($dir.'/*') as $file) {
            if (filemtime($file) < time() - $seconds && !is_dir($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Remove all the directories located in the specified directory
     * and their files.
     *
     * @param string $parent_dir The directory containing the dirs to remove.
     * @param int    $minutes    the time in minutes.
     *
     * @throws InvalidArgumentException if the directory does not exist, is outside
     *                                  the document root, or if the time in seconds is less than 0.
     */
    public static function deleteDirectoriesOlderThan($parent_dir, $minutes)
    {
        $documentRoot = $_SERVER['DOCUMENT_ROOT'];
        if (!StringUtils::contains($parent_dir, $documentRoot)) {
            throw new InvalidArgumentException('The directory must be located in the document root.');
        }

        if (!file_exists($parent_dir)) {
            throw new InvalidArgumentException('The specified directory is not valid.');
        }

        if (!is_int($minutes) || $minutes < 0) {
            throw new InvalidArgumentException('The second argument must be a positive integer.');
        }

        OS::execute("find $parent_dir/* -type d -cmin +$minutes | xargs rm -rf");
    }

    /**
     * Converts a Unix path to the URL from the DOCUMENT_ROOT.
     *
     * i.e. <code>/var/www/html/index.php -> /index.php</code>
     *
     * @param $path string path to convert.
     *
     * @throws Exception if the path is not inside the DOCUMENT_ROOT.
     *
     * @return string host url.
     */
    public static function pathToHostUrl($path)
    {
        $documentRoot = $_SERVER['DOCUMENT_ROOT'];

        if (strpos($path, $documentRoot) !== 0) {
            throw new Exception("The path '$path' is not part of the document root '$documentRoot'");
        }

        return str_replace($documentRoot, '', $path);
    }

    /**
     * Performs the operations of #pathToHostUrl() only if necessary.
     *
     * @param $path string non-normalised url.
     *
     * @return string string normalised url.
     */
    public static function normaliseUrl($path)
    {
        $path = stripslashes($path);

        if (strpos($path, '//') === 0) {
            // Normalises URLs like //url.com
            $path = 'http:'.$path;
        } elseif (strpos($path, '/') === 0) {
            // Prevents unix path to be passed to the program
            $path = $_SERVER['DOCUMENT_ROOT'].substr($path, 0);
        }

        return $path;
    }
}
