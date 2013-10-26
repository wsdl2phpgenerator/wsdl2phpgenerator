<?php
/**
 * @package phpSource
 */
namespace Wsdl2PhpGenerator\PhpSource;

/**
 * Abstract base class for all PHP elements, variables, functions and classes etc.
 *
 * @package phpSource
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
abstract class PhpElement
{
    /**
     *
     * @var string The access of the function |public|private|protected
     * @access protected
     */
    protected $access;

    /**
     *
     * @var string The identifier of the element
     * @access protected
     */
    protected $identifier;

    /**
     *
     * @var string The string to use for indention for the element
     */
    protected $indentionStr;

    /**
     * Function to be overloaded, return the source code of the specialized element
     *
     * @access public
     * @return string
     */
    abstract public function getSource();

    /**
     *
     * @return string The access of the element
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     *
     * @return string The identifier, name, of the element
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     *
     * @return string Returns the indention string
     */
    public function getIndentionStr()
    {
        return $this->indentionStr;
    }

    /**
     *
     * @param string $indentionStr Sets the indention string to use
     */
    public function setIndentionStr($indentionStr)
    {
        $this->indentionStr = $indentionStr;
    }

    /**
     * Takes a string and prepends ith with the current indention string
     * Has support for multiple lines
     *
     * @param string $source
     * @return string
     */
    public function getSourceRow($source)
    {
        if (strpos($source, PHP_EOL) === false) {
            return $this->indentionStr . $source . PHP_EOL;
        }

        $ret = '';
        $rows = explode(PHP_EOL, $source);
        if (strlen(trim($rows[0])) == 0) {
            $rows = array_splice($rows, 1);
        }
        if (strlen(trim($rows[(count($rows) - 1)])) == 0) {
            $rows = array_splice($rows, 0, count($rows) - 1);
        }
        foreach ($rows as $row) {
            $ret .= $this->indentionStr . $row . PHP_EOL;
        }

        return $ret;
    }
}
