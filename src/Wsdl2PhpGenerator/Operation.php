<?php
/**
 * @package Wsdl2PhpGenerator
 */
namespace Wsdl2PhpGenerator;

/**
 * Very stupid datatype to use instead of array
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Operation
{
    /**
     * @var string The name of the operation
     */
    private $name;

    /**
     * @var array An array with Variables
     * @see Variable
     */
    private $params;

    /**
     * @var string A description of the operation
     */
    private $description;

    /**
     * @var string A description of the operation
     */
    private $returns;

    /**
     *
     * @param string $name
     * @param string $paramStr The parameter string for a operation from the wsdl
     * @param string $description
     * @param string $returns
     */
    public function __construct($name, $paramStr, $description, $returns)
    {
        $this->name = $name;
        $this->params = array();
        $this->description = $description;
        $this->returns = $returns;

        $this->generateParams($paramStr);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getReturns()
    {
        return $this->returns;
    }

    /**
     * @param Type[] $validTypes An array of Type objects with valid types for typehinting
     * @return string A parameter string
     */
    public function getParamString(array $validTypes)
    {
        $params = array();

        foreach ($this->params as $value => $typeHint) {
            $ret = '';

            // Array or complex types is valid typehints
            if ($typeHint == 'array') {
                $ret .= $typeHint . ' ';
            } else {
                foreach ($validTypes as $type) {
                    if ($type instanceof ComplexType) {
                        if ($typeHint == $type->getPhpIdentifier()) {
                            $ret .= $typeHint . ' ';
                            break;
                        }
                    }
                }
            }

            $ret .= $value;

            if (strlen(trim($ret)) > 0) {
                $params[] = $ret;
            }
        }

        return implode(', ', $params);
    }

    /**
     *
     * @param string $name The param to get
     * @param array An array of Type objects with valid types for typehinting
     * @return array A array with three keys 'type' => the typehint to use 'name' => the name of the param and 'desc' => A description of the param
     */
    public function getPhpDocParams($name, array $validTypes)
    {
        $ret = array();

        $ret['desc'] = '';

        $paramType = '';
        foreach ($this->params as $value => $typeHint) {
            if ($name == $value) {
                $paramType = $typeHint;
            }
        }

        $ret['type'] = $paramType;

        foreach ($validTypes as $type) {
            if ($paramType == $type->getIdentifier()) {
                if ($type instanceof Pattern) {
                    $ret['type'] = $type->getDatatype();
                    $ret['desc'] = 'Restriction pattern: ' . $type->getValue();
                } else {
                    $ret['type'] = $type->getPhpIdentifier();

                    if ($type instanceof Enum) {
                        $ret['desc'] = 'Constant: ' . $type->getDatatype() . ' - ' . 'Valid values: ' . $type->getValidValues();
                    }
                }
            }
        }

        $ret['name'] = $name;

        return $ret;
    }

    /**
     *
     * @return string A parameter string
     */
    public function getParamStringNoTypeHints()
    {
        return implode(', ', array_keys($this->params));
    }

    /**
     * @return array Returns the parameter array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     *
     * @param string $paramStr A comma separated list of parameters with optional type hints
     */
    private function generateParams($paramStr)
    {
        $this->params = array();

        foreach (explode(', ', $paramStr) as $param) {
            $arr = explode(' ', $param);

            // Check if we have type hint. 1 = no type hint
            if (count($arr) == 1) {
                if (strlen($arr[0]) > 0) {
                    $this->params[$arr[0]] = '';
                }
            } else {
                $this->params[$arr[1]] = $arr[0];
            }
        }
    }
}
