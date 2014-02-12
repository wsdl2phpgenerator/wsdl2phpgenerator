<?php

/**
 * @package Wsdl2PhpGenerator
 */
namespace Wsdl2PhpGenerator;

use \InvalidArgumentException;
use Wsdl2PhpGenerator\PhpSource\PhpClass;

/**
 * Enum represents a simple type with enumerated values
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Enum extends Type
{
    /**
     * @var array The values in the enum
     */
    private $values;

    /**
     * Construct the object
     *
     * @param ConfigInterface $config The configuration
     * @param string $name The identifier for the class
     * @param string $restriction The restriction(datatype) of the values
     */
    public function __construct(ConfigInterface $config, $name, $restriction)
    {
        parent::__construct($config, $name, $restriction);
        $this->values = array();
    }

    /**
     * Implements the loading of the class object
     *
     * @throws Exception if the class is already generated(not null)
     */
    protected function generateClass()
    {
        if ($this->class != null) {
            throw new Exception("The class has already been generated");
        }

        $this->class = new PhpClass($this->phpIdentifier, $this->config->getClassExists());

        $first = true;

        foreach ($this->values as $value) {
            $name = Validator::validateNamingConvention($value);

            if (Validator::isKeyword($name)) {
                // TODO: Custom seems like a poor suffix for constant names
                // that collide with PHP keywords by default but is kept for
                // backwards compatibility for generated code.
                // Consider changing this for 3.x.
                $name .= 'Custom';
            }

            if ($first) {
                $this->class->addConstant($name, '__default');
                $first = false;
            }

            $this->addClassConstant($value, $name);
        }
    }

    /**
     * Try to generate an alternative constant name in case of collision
     *
     * @param string $name
     * @access protected
     * @throws Exception
     */
    protected function addClassConstant($value, $name)
    {
        $i = 2;
        $newName = $name;
        $lastException = null;
        do {
            try {
                $this->class->addConstant($value, $newName);
                break;
            } catch (Exception $e) {
                $newName = $name . '_' . $i++;
                $lastException = $e;
            }
        } while ($i <= 10);

        if ($i > 10) {
            throw $lastException;
        }
    }

    /**
     * Adds the value, typechecks strings and integers.
     * Otherwise it only checks so the value is not null
     *
     * @param mixed $value The value to add
     * @throws InvalidArgumentException if the value doesn'nt fit in the restriction
     */
    public function addValue($value)
    {
        if ($this->datatype == 'string') {
            if (is_string($value) == false) {
                throw new InvalidArgumentException('The value(' . $value . ') is not string but the restriction demands it');
            }
        } elseif ($this->datatype == 'integer') {
            // The value comes as string from the wsdl
            if (is_string($value)) {
                $value = intval($value);
            }

            if (is_int($value) == false) {
                throw new InvalidArgumentException('The value(' . $value . ') is not int but the restriction demands it');
            }
        } else {
            if ($value == null) {
                throw new InvalidArgumentException('Value(' . $value . ') is null');
            }
        }

        $this->values[] = $value;
    }

    /**
     * Returns a comma separated list of all the possible values for the enum
     *
     * @return string
     */
    public function getValidValues()
    {
        $ret = '';
        foreach ($this->values as $value) {
            $ret .= $value . ', ';
        }

        $ret = substr($ret, 0, -2);

        return $ret;
    }
}
