<?php
/**
 * @package phpSource
 */
namespace Wsdl2PhpGenerator\PhpSource;

/**
 * Class that represents a element (var, param, throws etc.) in a comment in php
 *
 * @package phpSource
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class PhpDocElement
{
    /**
     *
     * @var string The type of element
     */
    private $type;

    /**
     *
     * @var string The name of the datatype
     */
    private $datatype;

    /**
     *
     * @var string The name of the variable it represents
     */
    private $variableName;

    /**
     *
     * @var string The description
     */
    private $description;

    /**
     *
     * @param string $type
     * @param string $dataType
     * @param string $variableName
     * @param string $description
     */
    public function __construct($type, $dataType, $variableName, $description)
    {
        $this->type = $type;
        $this->datatype = $dataType;
        $this->variableName = $variableName;
        $this->description = $description;
    }

    /**
     * Returns the whole row of generated comment source
     *
     * @access public
     * @return string
     */
    public function getSource()
    {
        $ret = ' * ';

        $ret .= '@' . $this->type;

        if (strlen($this->datatype) > 0) {
            $ret .= ' ' . $this->datatype;
        }

        if (strlen($this->variableName) > 0) {
            $ret .= ' $' . $this->variableName;
        }

        if (strlen($this->description) > 0) {
            $ret .= ' ' . $this->description;
        }

        $ret .= PHP_EOL;

        return $ret;
    }

    /**
     *
     * @return string Returns the type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * @return string Returns the datatype
     */
    public function getDatatype()
    {
        return $this->datatype;
    }

    /**
     *
     * @return string Returns the identifier
     */
    public function getVariableName()
    {
        return $this->variableName;
    }

    /**
     *
     * @return string Returns the description
     */
    public function getDescription()
    {
        return $this->description;
    }
}
