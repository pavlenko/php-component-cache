<?php
/**
 * SunNY Creative Technologies
 *
 *   #####                                ##     ##    ##      ##
 * ##     ##                              ###    ##    ##      ##
 * ##                                     ####   ##     ##    ##
 * ##           ##     ##    ## #####     ## ##  ##      ##  ##
 *   #####      ##     ##    ###    ##    ##  ## ##       ####
 *        ##    ##     ##    ##     ##    ##   ####        ##
 *        ##    ##     ##    ##     ##    ##    ###        ##
 * ##     ##    ##     ##    ##     ##    ##     ##        ##
 *   #####        #######    ##     ##    ##     ##        ##
 *
 * C  R  E  A  T  I  V  E     T  E  C  H  N  O  L  O  G  I  E  S
 */

namespace PE\Component\Cache\Driver;

class FilesystemDriver extends AbstractDriver
{
    /**
     * @var string
     */
    private $directory;

    /**
     * @var \DateInterval|int
     */
    private $ttl;

    /**
     * @var int
     */
    private $umask;

    /**
     * @var int
     */
    private $directoryStringLength;

    /**
     * @var bool
     */
    private $isRunningOnWindows;

    /**
     * Constructor.
     *
     * @param string            $directory The cache directory.
     * @param int|\DateInterval $ttl       The cache default lifetime, week by default.
     * @param int               $umask     The cache directory access umask.
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($directory, $ttl = 604800, $umask = 0002)
    {
        if (!is_int($ttl) && !($ttl instanceof \DateInterval)) {
            throw new Exception\InvalidArgumentException('Time must be null or int or instance of DateInterval');
        }

        $this->ttl = $ttl;

        // YES, this needs to be *before* createPathIfNeeded()
        if (!is_int($umask)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The umask parameter is required to be integer, was: %s',
                gettype($umask)
            ));
        }

        $this->umask = $umask;

        if (!$this->createPathIfNeeded($directory)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The directory "%s" does not exist and could not be created.',
                $directory
            ));
        }

        // @codeCoverageIgnoreStart
        if (!is_writable($directory)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The directory "%s" is not writable.',
                $directory
            ));
        }
        // @codeCoverageIgnoreEnd

        // YES, this needs to be *after* createPathIfNeeded()
        $this->directory = realpath($directory);

        $this->directoryStringLength = strlen($this->directory);
        $this->isRunningOnWindows    = defined('PHP_WINDOWS_VERSION_BUILD');
    }

    /**
     * @inheritdoc
     */
    public function get($key, $default = null)
    {
        $this->validateKey($key);

        $data     = '';
        $lifetime = -1;
        $filename = $this->getFilename($key);

        if (!is_file($filename)) {
            return $default;
        }

        $resource = fopen($filename, 'rb');

        if (false !== ($line = fgets($resource))) {
            $lifetime = (int) $line;
        }

        if ($lifetime !== 0 && $lifetime < time()) {
            fclose($resource);
            @unlink($filename);
            return $default;
        }

        while (false !== ($line = fgets($resource))) {
            $data .= $line;
        }

        fclose($resource);

        return unserialize($data);
    }

    /**
     * @inheritdoc
     *
     * @param null|int|\DateInterval $ttl
     */
    public function set($key, $value, $ttl = null)
    {
        $this->validateKey($key);

        if ($ttl === null) {
            $ttl = $this->ttl;
        }

        if ($ttl instanceof \DateInterval) {
            $ttl = date_create('@0')->add($ttl)->getTimestamp();
        }

        if ($ttl > 0) {
            $ttl = time() + $ttl;
        }

        $data      = serialize($value);
        $filename  = $this->getFilename($key);

        return $this->writeFile($filename, $ttl . PHP_EOL . $data);
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        $this->validateKey($key);

        $filename = $this->getFilename($key);

        return @unlink($filename) || ! file_exists($filename);
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $name => $file) {
            if ($file->isDir()) {
                // Remove the intermediate directories which have been created to balance the tree. It only takes effect
                // if the directory is empty.
                @rmdir($name);
            } else {
                @unlink($name);
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function has($key)
    {
        $this->validateKey($key);

        $lifetime = -1;
        $filename = $this->getFilename($key);

        if (!is_file($filename)) {
            return false;
        }

        $resource = fopen($filename, 'rb');

        if (false !== ($line = fgets($resource))) {
            $lifetime = (int) $line;
        }

        fclose($resource);

        return $lifetime === 0 || $lifetime > time();
    }

    /**
     * @param string $id
     *
     * @return string
     */
    private function getFilename($id)
    {
        $hash = hash('sha256', $id);

        // This ensures that the filename is unique and that there are no invalid chars in it.
        if (
            '' === $id
            || ((strlen($id) * 2) > 255)
            || ($this->isRunningOnWindows && ($this->directoryStringLength + 4 + strlen($id) * 2) > 258)
        ) {
            // Most filesystems have a limit of 255 chars for each path component. On Windows the the whole path is limited
            // to 260 chars (including terminating null char). Using long UNC ("\\?\" prefix) does not work with the PHP API.
            // And there is a bug in PHP (https://bugs.php.net/bug.php?id=70943) with path lengths of 259.
            // So if the id in hex representation would surpass the limit, we use the hash instead. The prefix prevents
            // collisions between the hash and bin2hex.
            $filename = '_' . $hash;
        } else {
            $filename = bin2hex($id);
        }

        return $this->directory
            . DIRECTORY_SEPARATOR
            . substr($hash, 0, 2)
            . DIRECTORY_SEPARATOR
            . $filename;
    }

    /**
     * Create path if needed.
     *
     * @param string $path
     * @return bool TRUE on success or if path already exists, FALSE if path cannot be created.
     */
    private function createPathIfNeeded($path)
    {
        if (is_dir($path)) {
            return true;
        }

        if (false === @mkdir($path, 0777 & (~$this->umask), true) && !is_dir($path)) {
            return false;
        }

        return true;
    }

    /**
     * Writes a string content to file in an atomic way.
     *
     * @param string $filename Path to the file where to write the data.
     * @param string $content  The content to write
     *
     * @return bool TRUE on success, FALSE if path cannot be created, if path is not writable or an any other error.
     */
    protected function writeFile($filename, $content)
    {
        // @codeCoverageIgnoreStart
        $path = pathinfo($filename, PATHINFO_DIRNAME);

        if (!$this->createPathIfNeeded($path)) {
            return false;
        }

        if (!is_writable($path)) {
            return false;
        }

        $tmpFile = tempnam($path, 'swap');
        @chmod($tmpFile, 0666 & (~$this->umask));

        if (file_put_contents($tmpFile, $content) !== false) {
            if (@rename($tmpFile, $filename)) {
                return true;
            }

            @unlink($tmpFile);
        }

        return false;
        // @codeCoverageIgnoreEnd
    }
}