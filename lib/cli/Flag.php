<?php
/**
 * @package cli
 */

/**
 * Class that represents a flag in the cli, can have aliases and a description
 *
 * @package cli
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Flag
{
    /**
     *
     * @var string The flag itself
     */
    private $name;

    /**
     *
     * @var array Array of all aliases for the flag
     */
    private $aliases;

    /**
     *
     * @var string A description of the flag
     */
    private $description;

    /**
     *
     * @var bool If the flag has o have a parameter ot not
     */
    private $isBool;

    /**
     *
     * @var int The max number of alias
     */
    private $maxNumAliases;

    /**
     *
     * @param string $name
     * @param string $description
     * @param bool $isBool
     */
    public function __construct($name, $description, $isBool = false)
    {
        $this->name = $name;
        $this->description = $description;
        $this->isBool = $isBool;
        $this->aliases = array();
        $this->maxNumAliases = 4;
        // Based on the longest one : "-c, --classes, --classNames, --classList"
        $this->maxLength = 41; // TODO : must be computed
    }

    /**
     *
     * @return string Returns the string representation of the flag
     */
    public function __toString()
    {
        $maxWidth = getenv('COLUMNS') ? getenv('COLUMNS') : 80;
        $switchString = implode(', ', array_merge(array($this->name), $this->aliases));
        $switchString .= str_repeat(' ', 42 - strlen($switchString));

        return $switchString . str_replace("\n", "\n" . str_repeat(' ', strlen($switchString)), $this->description) . PHP_EOL;
    }

    /**
     *
     * @param string $alias
     *
     * @throws Exception Throws a exception if the alias already exists or if the maximum number of alias is reached
     *
     * @return void
     */
    public function addAlias($alias)
    {
        if (in_array($alias, $this->aliases)) {
            throw new Exception('Flag (' . $alias . ') is already a alias for ' . $this->name . '!');
        }

        if (count($this->aliases) >= $this->maxNumAliases) {
            throw new Exception('The maximum number of aliases have been reached');
        }

        $this->aliases[] = $alias;
    }

    /**
     *
     * @return string Returns the name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @return array Returns all aliases
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     *
     * @return bool
     */
    public function isBool()
    {
        return $this->isBool;
    }
}
