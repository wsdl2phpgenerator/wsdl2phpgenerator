<?php
/**
 * @package cli
 */

/**
 * Include the needed files
 */
require_once dirname(__FILE__) . '/CliParser.php';
require_once dirname(__FILE__) . '/Flag.php';

/**
 * Class that represents the command line interface
 *
 * @package cli
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Cli extends CliParser
{
    /**
     *
     * @var string The name of the executable
     */
    private $programName;

    /**
     *
     * @var string A usage string for the program eg. xvf file of the tar function
     */
    private $usageString;

    /**
     *
     * @var string The version of the program
     */
    private $version;

    /**
     *
     * @var array An array of accepted Flags, all the flags that have a meaning
     */
    private $acceptedFlags;

    /**
     *
     * @var array An array of the requred Flag objects
     */
    private $requiredFlags;

    /**
     *
     * @param string $programName
     * @param string $usage
     * @param string $version
     */
    public function __construct($programName, $usage, $version)
    {
        parent::__construct();

        $this->programName = $programName;
        $this->usageString = $usage;
        $this->version = $version;
        $this->acceptedFlags = array();
        $this->requiredFlags = array();
    }

    /**
     * Adds the flag as accepted
     *
     * @param string $flag
     * @param string $description
     * @param bool $isBool If the flag has to have a parameter
     * @param bool $reqired If the flag is required
     *
     * @throws Exception If the flag is already used
     *
     * @return void
     */
    public function addFlag($flag, $description, $isBool = false, $required = false)
    {
        // Check main name
        if (array_key_exists($flag, $this->acceptedFlags)) {
            throw new Exception('Flag (' . $flag . ') is already mapped!');
        }

        // Check all aliases
        foreach ($this->acceptedFlags as $value) {
            foreach ($value->getAliases() as $alias) {
                if ($alias == $flag) {
                    throw new Exception('Flag (' . $flag . ') is already mapped!');
                }
            }
        }

        // Not busy, add it
        $this->acceptedFlags[$flag] = new Flag($flag, $description, $isBool);

        if ($required) {
            $this->requiredFlags[$flag] = $this->acceptedFlags[$flag];
        }
    }

    /**
     *
     * @param string $flag
     * @param string $alias
     *
     * @throws Exception Throws exception if the alias is used already or if the flag isn't mapped
     *
     * @return void
     */
    public function addAlias($flag, $alias)
    {
        if (array_key_exists($flag, $this->acceptedFlags)) {
            // Check all aliases
            foreach ($this->acceptedFlags as $f) {
                foreach ($f->getAliases() as $a) {
                    if ($a == $alias) {
                        throw new Exception('Flag (' . $alias . ') is already mapped!');
                    }
                }
            }

            $this->acceptedFlags[$flag]->addAlias($alias);
        } else {
            throw new Exception('Flag (' . $flag . ') is not mapped!');
        }
    }

    /**
     * Show which params to send to the Cli and terminate
     */
    public function showUsage()
    {
        print _('Usage: ') . $this->programName . ' ' . $this->usageString . PHP_EOL;

        foreach ($this->acceptedFlags as $flag) {
            print $flag;
        }

        print _('Version: ') . $this->version . PHP_EOL;

        print PHP_EOL;
        exit;
    }

    /**
     * Parses and validates the flags according to the rules set up
     * Shows usage and terminates if the validation fails
     *
     * @param array $argv The array to parse
     */
    public function validate(array $argv)
    {
        $this->parse($argv);

        // Add the help flag if not defined
        if (array_key_exists('-h', $this->acceptedFlags) === false) {
            $this->acceptedFlags['-h'] = new Flag('-h', _('Help'), true);
        }

        if ($this->getValue('-h')) {
            $this->showUsage();
        }

        foreach ($this->requiredFlags as $flag) {
            if (array_key_exists($flag->getName(), $this->flags) == false) {
                $showError = true;
                foreach ($flag->getAliases() as $alias) {
                    if (array_key_exists($alias, $this->flags) == true) {
                        $showError = false;
                    }
                }

                if ($showError) {
                    print _('Required parameter missing!') . PHP_EOL;
                    $this->showUsage();
                }
            }
        }

        foreach ($this->flags as $key => $value) {
            $flag = $this->getFlag($key);

            if ($flag) {
                if ($flag->isBool() === false && $value === true) {
                    print _('A flag that must have a parameter does not') . PHP_EOL;
                    $this->showUsage();
                }
            }
        }
    }

    /**
     * Overrides base class, takes aliases into account
     *
     * @param string $flag
     *
     * @return string|bool Returns the value of the flag, string or bool according to the flag
     */
    public function getValue($flag)
    {
        $f = $this->getFlag($flag);

        if ($f) {
            if (array_key_exists($flag, $this->flags)) {
                return parent::getValue($f->getName());
            }
        }

        return false;
    }

    /**
     *
     * @param string $flag
     * @return Flag|null Returns the flag either by name or alias
     */
    private function getFlag($flag)
    {
        if (array_key_exists($flag, $this->acceptedFlags) == true) {
            return $this->acceptedFlags[$flag];
        } else {
            foreach ($this->acceptedFlags as $f) {
                if (in_array($flag, $f->getAliases()) == true) {
                    return $f;
                }
            }
        }

        return null;
    }
}
