<?php
/**
 * @package cli
 */

/**
 * Class that contains functions for parsing a array, based on the $argv array
 *
 * @package cli
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class CliParser
{
    /**
     * Flags is stored flag => value
     *
     * @var array The flags parsed from the command line
     * @access protected
     */
    protected $flags;

    /**
     * Construct the object
     */
    public function __construct()
    {
        $this->flags = array();
    }

    /**
     * @param array $argv The array to parse
     *  This is usually $argv in PHP, but perhaps you want to manipulate it before parsing it
     * @access public
     * @return void
     */
    public function parse(array $argv)
    {
        for ($i = 0; $i < count($argv); $i++) {
            $str = $argv[$i];

            // If we have a -- flag (double dash)
            if (strlen($str) > 2 && substr($str, 0, 2) == '--') {
                $parts = explode('=', $str);
                $flag_name = $parts[0];
                $flag_value = true;

                // Does not have an =, so choose the next arg as its value if it isn't a flag and exists
                if (count($parts) == 1 && isset($argv[$i + 1]) && preg_match('/^--?.+/', $argv[$i + 1]) == 0) {
                    $flag_value = $argv[$i + 1];
                } elseif (count($parts) == 2) { // Has a =, so pick the second piece
                    $flag_value = $parts[1];
                }
            } // If we have a ordinary - flag
            elseif (strlen($str) == 2 && $str[0] == '-') {
                $flag_name = $str;
                $flag_value = true;
                // Check if we want to set a value to the flag
                if (isset($argv[$i + 1]) && preg_match('/^--?.+/', $argv[$i + 1]) == 0) {
                    $flag_value = $argv[$i + 1];
                }
            } // If we have multiple flags with one dash
            elseif (strlen($str) > 1 && $str[0] == '-') {
                // If we don't have a = we have a multiple flags situation
                if (strpos($str, '=') === false) {
                    for ($j = 1; $j < strlen($str); $j++) {
                        $this->flags['-' . $str[$j]] = true;
                    }
                } else {
                    $parts = explode('=', $str);
                    $flag_name = $parts[0];
                    $flag_value = true;

                    if (count($parts) == 2) { // Has a =, so pick the second piece
                        $flag_value = $parts[1];
                    }
                }
            }
            if (isset($flag_name) && isset($flag_value)) {
                if (isset($this->flags[$flag_name]) && ($this->flags[$flag_name] !== true)) {
                    if (is_array($this->flags[$flag_name])) {
                        $this->flags[$flag_name][] = $flag_value;
                    } else {
                        $this->flags[$flag_name] = array($this->flags[$flag_name]);
                        $this->flags[$flag_name][] = $flag_value;
                    }
                } else {
                    $this->flags[$flag_name] = $flag_value;
                }
                unset($flag_value);
                unset($flag_name);
            }
        }
    }

    /**
     *
     * @param string $flag
     * @return string|bool Returns false if the flag is not set, the value otherwise
     */
    public function getValue($flag)
    {
        return isset($this->flags[$flag]) ? $this->flags[$flag] : false;
    }
}
