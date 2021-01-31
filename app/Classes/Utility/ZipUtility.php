<?php

namespace CarstenWalther\XliffGen\Utility;

use RuntimeException;

/**
 * Class Zip
 *
 * Creates an uncompressed downloadable ZIP file without the need of temporary files.
 *
 * There are two ways to use this class:
 *
 *  1. Prepare all files and send them then (the archive size is calulated so the user will
 *     have a download progress) - Example:
 *
 *        $zip = new Zip('images.zip');
 *        $zip->addDir(dirname(__FILE__), true, '/\.(jpg|jpeg)/i'); // All JPEGs recursively
 *        $zip->addFile('/var/www/html/testdata.bin');              // Just a normal file
 *        $zip->addData('All the JPEG images.', 'desc.txt');        // A raw text file
 *        $zip->send();
 *
 *  2. Send everything as you go (final size is not known so the user will know the end only
 *     by the connection being closed) - Example:
 *
 *        Zip::begin('images.zip');
 *        Zip::sendDir(dirname(__FILE__), true, '/\.(jpg|jpeg)/i'); // All JPEGs recursively
 *        Zip::sendFile('/var/www/html/testdata.bin');              // Just a normal file
 *        Zip::sendData('All the JPEG images.', 'desc.txt');        // A raw text file
 *        Zip::end();
 *
 * @package CarstenWalther\XliffGen\Utility
 */
class ZipUtility
{
    /**
     * The internal buffer size for file uploads
     */
    public const BUFFER_SIZE = 0x10000; // 64 KB

    public const CURRENT_FILE_COUNT = 0;

    private static $currCentralDir = '';

    private static $currLength = 0;

    private static $currFileCount = 0;

    private static $begun = false;

    /**
     * The name of the ZIP file when sent to the client
     *
     * @var string
     */
    public $zipName = 'download.zip';

    /**
     * An optional comment for the ZIP file
     *
     * @var mixed|string
     */
    public $comment = '';

    /**
     * The collection of file entries to send (use with care!!!)
     *
     * @var array {
     *     array {
     *         'file' =>         The full path of the file
     *         'name' =>         The filename including the relative pathinfo
     *         'comment' =>      Empty
     *     }
     * }
     */
    public $files = [];

    /**
     * @param string $zipName The name of the ZIP file when sent to the client
     * @param string @comment   An optional comment for the ZIP file
     */
    public function __construct($zipName = 'download.zip', $comment = '')
    {
        $this->zipName = $zipName;
        $this->comment = $comment;
    }

    /**
     * Start sending a ZIP archive by setting the headers
     *
     * @param string $zipName       The filename of the ZIP archive for the downloader
     * @param bool   $unlimitedTime If true, the time limit will be set to 0
     */
    public static function begin($zipName = 'downlaod.zip', $unlimitedTime = true) : void
    {
        // Don't let the timeout cut the connection
        if ($unlimitedTime === true) {
            set_time_limit(0);
        }

        // Reset global counters
        self::$currCentralDir = '';
        self::$currLength = 0;
        self::$currFileCount = 0;

        // Set headers
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT', true, 200);
        header('Content-Type: ' . 'application/zip');
        header('Content-Disposition: inline; filename="' . $zipName . '"');

        self::$begun = true;
    }

    /**
     * Appends the files of a directory to the archive stream
     *
     * @param string $path      The full path to a directory
     * @param bool   $recursive If true, the path will be searched recursively
     * @param null   $filter    If not null, only files which match this Regex will be added
     *
     * @throws \Exception
     */
    public static function sendDir(string $path, $recursive = true, $filter = null) : void
    {
        if (!self::$begun) {
            throw new RuntimeException('Begin has not been called yet!');
        }
        $files = self::getFiles($path, $recursive, '', $filter);
        foreach ($files as $file) {
            self::sendFile($file['file'], $file['name'], '', '');
        }
    }

    /**
     * Returns a list of file entries within a directory
     *
     * @param string $path      The full path of the directory to get the files from
     * @param bool   $recursive If true, subdirectories will be searched as well using relative paths for the name
     * @param string $relativePath
     * @param null   $filter    If not null, only files which match this Regex will be added
     *
     * @return array
     */
    private static function getFiles(string $path, $recursive = true, $relativePath = '', $filter = null) : array
    {
        // Unify path
        if ($path[strlen($path) - 1] !== '/') {
            $path .= '/';
        }

        // Get directory files and sort them
        $entries = scandir($path . $relativePath);
        sort($entries);

        $result = [];
        $files = [];
        foreach ($entries as $name) {
            $name = preg_replace('/[\t\r\n]/', '', $name);
            if ($name === '.' || $name === '..') {
                continue;
            }
            // Get full filename
            $filename = $path . $relativePath . $name;
            // Handle directories
            if (is_dir($filename)) {
                if ($recursive) {
                    // Get sub-directory entries
                    $result = array_merge($result, self::getFiles($path, true, $relativePath . $name . '/', $filter));
                }
            } elseif ($filter === null || preg_match($filter, $name)) {
                // Add files to temporary list so they'll list after all directories
                $files[] = ['file' => $filename, 'name' => $relativePath . $name, 'comment' => ''];
            }
        }

        // Add file entries to result
        $result = array_merge($result, $files);

        return $result;
    }

    /**
     * Appends a single file to the archive stream
     *
     * @param string $file         The full path of the file
     * @param null   $name         The file name (without any path), if null the basename of $file is used
     * @param string $relativePath The relative path of the file
     * @param string $comment      An optional comment for the file
     *
     * @throws \Exception
     */
    public static function sendFile(string $file, $name = null, $relativePath = '', $comment = '') : void
    {
        if (!self::$begun) {
            throw new RuntimeException('Begin has not been called yet!');
        }
        if ($relativePath !== '' && $relativePath[strlen($relativePath) - 1] !== '/') {
            $relativePath .= '/';
        }
        if ($name === null) {
            $name = basename($file);
        }

        $name = $relativePath . $name;
        $size = filesize($file);
        $time = filemtime($file);
        $hash = self::getFileHash($file);

        // Send header
        $head = self::getHeader($name, $size, $time, $hash);
        echo($head);

        // Stream file contents in BUFFER_SIZE chunks
        if (@$handle = fopen($file, 'rb')) {
            while (!feof($handle)) {
                $buffer = fread($handle, self::BUFFER_SIZE);
                echo($buffer);
                flush();
            }

            fclose($handle);
        }

        // Cache central directory
        self::$currCentralDir .= self::getCentralDirEntry($name, $size, $time, $hash, self::$currLength, [], $comment);

        // Adjust length
        self::$currLength += strlen($head) + $size;
        self::$currFileCount++;
    }

    /** Computes a CRC-32 checksum (with magical number) of a file
     *
     * @param $filename
     *
     * @return string
     */
    private static function getFileHash($filename) : string
    {
        return strrev(str_pad(hash_file("crc32b", $filename, true), 4, "\x00", STR_PAD_LEFT));
    }

    /**
     * Creates a file entry header
     *
     * @param string $name The name of the file including its relative path
     * @param int    $size The file size
     * @param int    $time The last modification time of the file as unix timestamp
     * @param string $hash The CRC-32 cecksum (4 bytes raw)
     *                     $param array  $extraData Additional file attributes {
     *                     string $key       The 2 byte identifier
     *                     string $value     The attributes
     *                     }
     * @param array  $extraData
     *
     * @return string
     * @throws \Exception
     */
    private static function getHeader(string $name, int $size, int $time, string $hash, $extraData = []) : string
    {
        $nlen = strlen($name);
        if ($nlen > 0xFFFF) {
            throw new RuntimeException('The name exceeds the maximum length of 65535 bytes!');
        }

        $extra = '';
        foreach ($extraData as $key => $val) {
            if (strlen($key) !== 2) {
                throw new RuntimeException('Extra data keys must be 2 bytes long!');
            }
            $l = strlen($val);
            if ($l > 65531) {
                throw new RuntimeException('Extra data value exceeds the maximum length of 65531 bytes!');
            }
            $extra .= $key . self::getUInt16($l) . $val;
        }
        $elen = strlen($extra);
        if ($elen > 0xFFFF) {
            throw new RuntimeException('Extra Part exceedes the maximum length of 65535 bytes!');
        }

        $head = "PK\x03\x04"; // Signature
        $head .= "\x0A\x00"; // Version 0.10
        $head .= "\x00\x08"; // Flags (0x0800: UTF-8 file names)
        $head .= "\x00\x00"; // Compression method (00: none)
        $head .= self::dateTimeToString($time); // Modification time and date
        $head .= $hash; // CRC32 hash
        $head .= self::getUInt32($size); // Compressed size
        $head .= self::getUInt32($size); // Un-compressed size
        $head .= self::getUInt16($nlen); // Filename length
        $head .= self::getUInt16($elen); // Extra data length
        $head .= $name; // Filename
        $head .= $extra; // Extra data fields

        return $head;
    }

    /**
     * Converts a 16 bit unsigned number to its little endian binary representation
     *
     * @param $val
     *
     * @return string
     */
    private static function getUInt16($val) : string
    {
        return chr($val & 0xFF) . chr(($val & 0xFF00) >> 8);
    }

    /**
     * Converts a unix timestamp to its little endian binary MSDOS representation
     *
     * @param null $val
     *
     * @return string
     */
    private static function dateTimeToString($val = null) : string
    {
        if ($val === null) {
            $val = time();
        }
        // Bits: YYYYYYYmmmmddddd HHHHHiiiiiisssss -> to bytes as little endian
        $parts = explode(',', date('H,i,s,Y,m,d', $val));
        $time = ((int)$parts[0] << 11) | ((int)$parts[1] << 5) | ((int)$parts[2] >> 1);
        $date = (((int)$parts[3] - 1980) << 9) | ((int)$parts[4] << 5) | (int)$parts[5];
        return self::getUInt16($time) . self::getUInt16($date);
    }

    /**
     * Converts a 32 bit unsigned number to its little endian binary representation
     *
     * @param $val
     *
     * @return string
     */
    private static function getUInt32($val) : string
    {
        return chr($val & 0xFF) . chr(($val & 0xFF00) >> 8) . chr(($val & 0xFF0000) >> 16) . chr(($val & 0xFF000000) >> 24);
    }

    /**
     * Creates a file entry for the central directory
     *
     * @param string $name    The name of the file including its relative path
     * @param int    $size    The file size
     * @param int    $time    The last modification time of the file as unix timestamp
     * @param string $hash    The CRC-32 cecksum (4 bytes raw)
     * @param int    $offset  The offset in the file where the header of the file starts
     *                        $param array  $extraData Additional file attributes {
     *                        string $key       The 2 byte identifier
     *                        string $value     The attributes
     *                        }
     * @param array  $extraData
     * @param string $comment An optional file comment
     * @param int    $disk    The index of the disk the file is on
     *
     * @return string
     * @throws \Exception
     */
    private static function getCentralDirEntry(string $name, int $size, int $time, string $hash, int $offset, $extraData = [], $comment = '', $disk = 0) : string
    {
        $nlen = strlen($name);
        if ($nlen > 0xFFFF) {
            throw new RuntimeException('The name exceeds the maximum length of 65535 bytes!');
        }

        $extra = '';
        foreach ($extraData as $key => $val) {
            if (strlen($key) !== 2) {
                throw new RuntimeException('Extra data keys must be 2 bytes long!');
            }
            $l = strlen($val);
            if ($l > 65531) {
                throw new RuntimeException('Extra data value exceeds the maximum length of 65531 bytes!');
            }
            $extra .= $key . self::getUInt16($l) . $val;
        }
        $elen = strlen($extra);
        if ($elen > 0xFFFF) {
            throw new RuntimeException('Extra Part exceedes the maximum length of 65535 bytes!');
        }

        $clen = strlen($comment);
        if ($clen > 0xFFFF) {
            throw new RuntimeException('The comment exceedes the maximum length of 65535 bytes!');
        }

        $head = "PK\x01\x02"; // Signature
        $head .= "\x3F\x00"; // OS version
        $head .= "\x0A\x00"; // Version needed
        $head .= "\x00\x08"; // Flags (0x0800: UTF-8 file names)
        $head .= "\x00\x00"; // Compression method (00: none)
        $head .= self::dateTimeToString($time); // Modification time and date
        $head .= $hash; // CRC32 hash
        $head .= self::getUInt32($size); // Compressed size
        $head .= self::getUInt32($size); // Un-compressed size
        $head .= self::getUInt16($nlen); // Filename length
        $head .= self::getUInt16($elen); // Extra data length
        $head .= self::getUInt16($clen); // Comment length
        $head .= self::getUInt16($disk); // Disk
        $head .= "\x00\x00"; // Internal attributes
        $head .= "\x00\x00\x00\x00"; // External attributes
        $head .= self::getUInt32($offset);
        $head .= $name; // Filename
        $head .= $extra; // Extra data fields
        $head .= $comment; // File comment

        return $head;
    }

    /**
     * Appends a single file from raw data to the archive stream
     *
     * @param string $data         The raw data of the file
     * @param string $name         The file name (without any path)
     * @param string $relativePath The relative path of the file
     * @param string $comment      An optional comment for the file
     * @param null   $filetime
     *
     * @throws \Exception
     */
    public static function sendData(string $data, string $name, $relativePath = '', $comment = '', $filetime = null) : void
    {
        if (!self::$begun) {
            throw new RuntimeException('Begin has not been called yet!');
        }
        if ($relativePath !== '' && $relativePath[strlen($relativePath) - 1] !== '/') {
            $relativePath .= '/';
        }
        if ($filetime === null) {
            $filetime = time();
        }

        $name = $relativePath . $name;
        $size = strlen($data);
        $hash = self::getDataHash($data);

        // Send header
        $head = self::getHeader($name, $size, $filetime, $hash);
        echo($head);

        // Send raw data
        echo($data);

        // Cache central directory
        self::$currCentralDir .= self::getCentralDirEntry($name, $size, $filetime, $hash, self::$currLength, [], $comment);

        // Adjust length
        self::$currLength += strlen($head) + $size;
        self::$currFileCount++;
    }

    /**
     * Computes a CRC-32 checksum (with magical number) of binary data
     *
     * @param $data
     *
     * @return string
     */
    private static function getDataHash($data) : string
    {
        return strrev(str_pad(hash("crc32b", $data, true), 4, "\x00", STR_PAD_LEFT));
    }

    /**
     * Sends the file end entry finishing the zip file upload
     *
     * @param string $comment An optional comment for the ZIP archive
     *
     * @throws \Exception
     */
    public static function end($comment = '') : void
    {
        if (!self::$begun) {
            throw new RuntimeException('Begin has not been called yet!');
        }
        if (self::$currFileCount === 0) {
            throw new RuntimeException('No file have been sent so there is nothing to end!');
        }

        // Send central directory
        $centralDir = self::$currCentralDir;
        echo($centralDir);

        // Send end of central directory
        $eof = self::getEndOfCentralDir(self::CURRENT_FILE_COUNT, strlen($centralDir), self::$currLength, self::CURRENT_FILE_COUNT, 0, 0, $comment);
        echo($eof);

        flush();
    }

    /**
     * Creates an end-of-central-directory entry
     *
     * @param int    $fileCount      The number of files in the central directory on this disk
     * @param int    $size           The length of the central directory
     * @param int    $offset         The offset of the central directory in this file
     * @param null   $totalFileCount The total number of files in the central directory
     * @param int    $disk           The disk index this central directory is on
     * @param int    $startDisk      The disk index on which the central directory starts
     * @param string $comment        An optional comment for the ZIP file
     *
     * @return string
     * @throws \Exception
     */
    private static function getEndOfCentralDir(int $fileCount, int $size, int $offset, $totalFileCount = null, $disk = 0, $startDisk = 0, $comment = '') : string
    {
        if ($totalFileCount === null) {
            $totalFileCount = $fileCount;
        }

        $clen = strlen($comment);
        if ($clen > 0xFFFF) {
            throw new RuntimeException('The comment exceedes the maximum length of 65535 bytes!');
        }

        $data = "PK\x05\x06"; // Signature
        $data .= self::getUInt16($disk); // Index of this disk
        $data .= self::getUInt16($startDisk); // Index of disk where this central dir starts
        $data .= self::getUInt16($fileCount); // The number of file entries in this central directory on this disk
        $data .= self::getUInt16($totalFileCount); // The total number of file entries in this central dir
        $data .= self::getUInt32($size); // The size of the central dir
        $data .= self::getUInt32($offset); // The offset where the directory starts (on the according disk)
        $data .= self::getUInt16($clen); // Length of the comment
        $data .= $comment;

        return $data;
    }

    /**
     * Adds the files of a directory to the file collection
     *
     * @param string $path      The full path to a directory
     * @param bool   $recursive If true, the path will be searched recursively
     * @param null   $filter    If not null, only files which match this Regex will be added
     */
    public function addDir(string $path, $recursive = true, $filter = null) : void
    {
        $files = self::getFiles($path, $recursive, '', $filter);
        $this->files = array_merge($this->files, $files);
    }

    /**
     * Adds a single file to the file collection
     *
     * @param string $file         The full path of the file
     * @param null   $name         The file name (without any path), if null the basename of $file is used
     * @param string $relativePath The relative path of the file
     * @param string $comment      An optional comment for the file
     */
    public function addFile(string $file, $name = null, $relativePath = '', $comment = '') : void
    {
        if ($relativePath !== '' && $relativePath[strlen($relativePath) - 1] !== '/') {
            $relativePath .= '/';
        }
        if ($name === null) {
            $name = basename($file);
        }
        $this->files[] = ['file' => $file, 'name' => $relativePath . $name, 'comment' => $comment];
    }

    /**
     * Adds a single file from raw data to the file collection
     *
     * @param string $data         The raw data of the file
     * @param string $name         The file name (without any path)
     * @param string $relativePath The relative path of the file
     * @param string $comment      An optional comment for the file
     * @param null   $filetime
     */
    public function addData(string $data, string $name, $relativePath = '', $comment = '', $filetime = null) : void
    {
        if ($relativePath !== '' && $relativePath[strlen($relativePath) - 1] !== '/') {
            $relativePath .= '/';
        }
        if ($filetime === null) {
            $filetime = time();
        }
        $this->files[] = ['data' => $data, 'name' => $relativePath . $name, 'comment' => $comment, 'time' => $filetime];
    }

    /**
     * Clears the file collection
     */
    public function clear() : void
    {
        $this->files = [];
    }

    /**
     * Collects all nesessary information and then starts sending the file collection as ZIP file to the client
     *
     * @throws \Exception
     */
    public function send() : void
    {
        // Don't let the timeout cut the connection
        set_time_limit(0);

        $offset = 0;
        $fileCount = 0;
        $centralDir = '';

        $infos = [];
        foreach ($this->files as $entry) {
            $info = [];
            $info['name'] = $entry['name'];
            $info['comment'] = $entry['comment'];

            // Get file information
            $name = $entry['name'];
            $size = 0;
            $time = 0;
            $hash = '';
            if (isset($entry['data'])) { // Content passed directly
                // Prepare header
                $info['data'] = $entry['data'];
                $data = $entry['data'];
                $size = strlen($data);
                $time = $entry['time'] ?? time();
                $hash = self::getDataHash($data);

                // Get header
                $head = self::getHeader($name, $size, $time, $hash);
                $info['head'] = $head;
            } else { // From file
                // Prepare header
                $info['file'] = $entry['file'];
                $file = $entry['file'];
                $size = filesize($file);
                $time = filemtime($file);
                $hash = self::getFileHash($file);

                // Get and send header
                $head = self::getHeader($name, $size, $time, $hash);
                $info['head'] = $head;
            }

            // Collect central directory entry for this file
            $cmt = $entry['comment'] ?? '';
            $centralDir .= self::getCentralDirEntry($name, $size, $time, $hash, $offset, [], $cmt);

            // Increese offset and file count
            $offset += strlen($head) + $size;
            $fileCount++;

            $infos[] = $info;
        }

        // Prepare end of central directory
        $cdlen = strlen($centralDir);
        $comment = $this->comment;
        $eof = self::getEndOfCentralDir($fileCount, $cdlen, $offset, $fileCount, 0, 0, $comment);

        $length = $offset + strlen($centralDir) + strlen($eof);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT', true, 200);
        header('Content-Length: ' . $length);
        header('Content-Type: ' . 'application/zip');
        header('Content-Disposition: inline; filename="' . $this->zipName . '"');

        foreach ($infos as $info) {
            // Send file header
            echo($info['head']);

            // Send file data
            if (isset($info['data'])) {
                // Send directly passed data
                echo($info['data']);
            } elseif (@$handle = fopen($info['file'], 'rb')) {
                // Stream file contents in BUFFER_SIZE chunks
                while (!feof($handle)) {
                    $buffer = fread($handle, self::BUFFER_SIZE);
                    echo($buffer);
                    flush();
                }
                fclose($handle);
            }
        }

        // Send central directory
        echo($centralDir);

        // Send end of central directory
        echo($eof);
    }
}
