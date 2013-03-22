<?php
/**
 * @package config
 */

/**
 * Include the interface
 */
require_once dirname(__FILE__) . '/IConfig.php';

/**
 * A implementation of Config using a flatfile for storage
 *
 * @package config
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class FileConfig implements IConfig
{
    /**
     * The separator in the file
     *
     * @var string
     */
    private $separator;

    /**
     * The file to use
     *
     * @var string
     */
    private $filename;

    /**
     * The number of values to use, default is 2, key, value
     *
     * @var int
     */
    private $limit;

    /**
     * If the values have been modified
     *
     * @var bool
     */
    private $modified;

    /**
     * If the values have been loaded
     *
     * @var bool
     */
    private $loaded;

    /**
     * If we should buffer the changes of flush them to file directly
     *
     * @var bool
     */
    private $buffer;

    /**
     * The values
     *
     * @var array
     */
    private $data;

    /**
     *
     * @var string The string that represents a comment, one character and should be the first character for the row
     */
    private $commentChar;

    /**
     * Constructs the fileconfig
     *
     * @param string $filename
     * @param bool $buffer
     * @param string $separator
     */
    public function __construct($filename, $buffer = false, $separator = '=')
    {
        $this->data = array();
        $this->filename = $filename;
        $this->separator = $separator;
        $this->limit = 2; // Key value pairs
        $this->modified = false;
        $this->loaded = false;
        $this->buffer = $buffer;
        $this->commentChar = '#'; // Hardcoded
        //TODO: Allow optional commentchars? ';' etc. ?
    }

    /**
     * Destructor that saves the values back to disk if modified
     */
    public function __destruct()
    {
        if ($this->buffer == true && $this->modified == true && $this->loaded == true) {
            $this->save();
        }
    }

    /**
     * Sets the value for key. If the value exists it is overwritten, else added.
     * Based on buffer the function also saves the new config to disk.
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public function set($key, $value)
    {
        if ($this->loaded == false) {
            $this->load();
        }

        $this->data[trim($key)] = strval($value);
        $this->modified = true;

        if ($this->buffer == false) {
            $this->save();
            $this->modified = false;
        }
    }

    /**
     * Returns the value attached to key or throw an exception
     * @param string $key
     * @return string
     * @throws Exception If the value does not exist in the config
     */
    public function get($key)
    {
        if ($this->exists($key)) {
            return $this->data[$key];
        } else {
            throw new Exception('Trying to get a nonexisting value!');
        }
    }

    /**
     * Checks if the key exists
     *
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        if ($this->loaded == false) {
            $this->load();
        }

        return array_key_exists($key, $this->data);
    }

    /**
     * Loads the config file if the file exists else throws an exception.
     *
     * @throws Exception If the file is invalid
     */
    private function load()
    {
        if (file_exists($this->filename)) {
            $contents = file($this->filename);

            foreach ($contents as $line) {
                // If we have a comment, skip the line
                if (trim($line[0]) == $this->commentChar || trim($line[0]) == '') {
                    continue;
                }

                $arr = explode($this->separator, $line, $this->limit);
                if (count($arr) != $this->limit) {
                    throw new Exception('Invalid config file');
                } else {
                    $this->data[$arr[0]] = trim($arr[1]);
                }
            }

            $this->loaded = true;
        } else {
            throw new Exception('File does not exist!');
        }
    }

    /**
     * Saves the config file if it is modified.
     * Owervrites the existing file if any·
     */
    private function save()
    {
        if ($this->modified) {
            $handle = fopen($this->filename, 'w');
            foreach ($this->data as $key => $value) {
                // Write each key value on each line
                fwrite($handle, trim($key) . trim($this->separator) . trim($value) . PHP_EOL);
            }
            fclose($handle);
        }
    }
}
