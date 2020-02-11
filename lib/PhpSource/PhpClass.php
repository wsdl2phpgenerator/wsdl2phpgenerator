<?php
/**
 * @package phpSource
 */
namespace Wsdl2PhpGenerator\PhpSource;

use Exception;

/**
 * Class that represents the source code for a class in php
 *
 * @package phpSource
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class PhpClass extends PhpElement
{
    /**
     *
     * @var string namespace rewrite (optional)
     * @access private
     */
    private $classNamespace = '';

    /**
     *
     * @var array An array of strings, contains all the filenames to include for the class
     * @access private
     */
    private $dependencies;

    /**
     *
     * @var array An array of strings, contains all the classes to import for the class
     * @access private
     */
    private $namespaces;

    /**
     *
     * @var bool If the class should be protected by a if(!class_exists() statement
     * @access private
     */
    private $classExists;

    /**
     *
     * @var bool If the class is final
     * @access private
     */
    private $final;

    /**
     *
     * @var string
     * @access private
     */
    private $extends;

    /**
     *
     * @var string[]
     * @access private
     */
    private $implements = [];

    /**
     *
     * @var string[]
     * @access private
     */
    private $traits;

    /**
     *
     * @var string
     * @access private
     */
    private $default;

    /**
     *
     * @var array Array of constants key = name of constant value = value of constant
     */
    private $constants = [];

    /**
     *
     * @var PhpVariable[]
     * @access private
     */
    private $variables = [];

    /**
     *
     * @var PhpFunction[]
     * @access private
     */
    private $functions = [];

    /**
     *
     * @var PhpDocComment A description of the class in phpdoc format
     * @access private
     */
    private $comment;

    /**
     *
     * @var bool If the class is abstract.
     * @access private
     */
    private $abstract;

    /**
     *
     * @param string $identifier
     * @param bool $classExists
     * @param string $extends A string of the class that this class extends
     * @param PhpDocComment $comment
     * @param bool $final
     * @param bool $abstract
     */
    public function __construct($identifier, $classExists = false, $extends = '', PhpDocComment $comment = null, $final = false, $abstract = false)
    {
        $this->dependencies = array();
        $this->namespaces = array();
        $this->classExists = $classExists;
        $this->comment = $comment;
        $this->final = $final;
        $this->identifier = $identifier;
        $this->access = '';
        $this->extends = $extends;
        $this->traits = array();
        $this->constants = array();
        $this->variables = array();
        $this->functions = array();
        $this->indentionStr = '    '; // Use 4 spaces as indention, as requested by PSR-2
        $this->abstract = $abstract;
    }

    /**
     *
     * @return string Returns the compete source code for the class
     */
    public function getSource()
    {
        $ret = '';

        if ($this->classExists) {
            $ret .= 'if (!class_exists("' . $this->identifier . '", false)) ' . PHP_EOL . '{' . PHP_EOL;
        }

        if (count($this->dependencies) > 0) {
            foreach ($this->dependencies as $file) {
                $ret .= 'include_once(\'' . $file . '\');' . PHP_EOL;
            }
            $ret .= PHP_EOL;
        }

        if (count($this->namespaces) > 0) {
            foreach ($this->namespaces as $namespace) {
                $ret .= 'use ' . $namespace . ';' . PHP_EOL;
            }
            $ret .= PHP_EOL;
        }

        if ($this->comment !== null) {
            $ret .= $this->comment->getSource();
        }

        if ($this->final) {
            $ret .= 'final ';
        }

        if ($this->abstract) {
            $ret .= 'abstract ';
        }

        $ret .= 'class ' . $this->identifier;

        if (strlen($this->extends) > 0) {
            $ret .= ' extends ' . $this->extends;
        }

        if (count($this->implements) > 0) {
            $ret .= ' implements ' . implode(', ', $this->implements);
        }

        $ret .= PHP_EOL . '{';

        if (count($this->traits) > 0) {
            $ret .= PHP_EOL;
            foreach ($this->traits as $trait) {
                $ret .= $this->getIndentionStr() . 'use ' . $trait . ';' . PHP_EOL;
            }
        }

        if (isset($this->default)) {
            $ret .= PHP_EOL;
            $ret .= $this->getIndentionStr() . 'const __default = ' . $this->default . ';' . PHP_EOL;
        }

        if (count($this->constants) > 0) {
            $ret .= PHP_EOL;
            foreach ($this->constants as $name => $value) {
                $ret .= $this->getIndentionStr() . 'const ' . $name . ' = ';
                if (is_array($value)) {
                    $options = explode(PHP_EOL, var_export($value, true));
                    $ret .= implode(PHP_EOL . $this->getIndentionStr(), $options) . ';' . PHP_EOL;
                } else {
                    $ret .= '\'' . $value . '\';' . PHP_EOL;
                }
            }
        }

        if (count($this->variables) > 0) {
            foreach ($this->variables as $variable) {
                $variable->setIndentionStr($this->getIndentionStr());
                $ret .= $variable->getSource();
            }
        }

        if (count($this->functions) > 0) {
            foreach ($this->functions as $function) {
                $function->setIndentionStr($this->getIndentionStr());
                $ret .= $function->getSource();
            }
        }

        $ret .= '}' . PHP_EOL;

        if ($this->classExists) {
            $ret .= PHP_EOL . '}' . PHP_EOL;
        }

        return $ret;
    }

    /**
     * Sets class namespace rewrite
     *
     * @param string $namespace
     * @return void
     */
    public function setClassNamespace($namespace)
    {
        $this->classNamespace = $namespace;
    }

    /**
     * Retrieve class namespace rewrite
     *
     * @return string
     */
    public function getClassNamespace()
    {
        return $this->classNamespace;
    }

    /**
     * Adds a dependency to be loaded for the class to use
     * Only adds it if it does not already exist
     *
     * @param string $filename
     */
    public function addDependency($filename)
    {
        if (in_array($filename, $this->dependencies) == false) {
            $this->dependencies[] = $filename;
        }
    }

    /**
     * Adds a class name to be imported for the class to use
     *
     * @param string $className
     */
    public function addNamespace($className)
    {
        if (in_array($className, $this->namespaces) == false) {
            $this->namespaces[] = $className;
        }
    }

    /**
     * @param string|\string[] $classes  $filename
     */
    public function addImplementation($classes)
    {
        $classes = (array)$classes;
        $this->implements = array_merge((array)$this->implements, $classes);
    }

    /**
     * Adds a trait import to the class
     *
     * @param string $value
     * @throws Exception
     */
    public function addTrait($value)
    {
        if (in_array($value, $this->traits)) {
            throw new Exception('A trait of the name (' . $value . ') does already exist.');
        }

        $this->traits[] = $value;
    }

    /**
     * Set default value
     *
     * @param $const
     */
    public function setDefault($const)
    {
        $this->default = $const;
    }

    /**
     * Adds a constant to the class. If no name is supplied and the value is a string the value is used as name otherwise exception is raised
     *
     * @param mixed $value
     * @param string|array $name
     * @throws Exception
     */
    public function addConstant($value, $name = '')
    {
        if ((is_string($value) && strlen($value) == 0) || is_array($value) && !$value) {
            throw new Exception('No value supplied');
        }

        // If no name is supplied use the value as name
        if (strlen($name) == 0) {
            if (is_string($value)) {
                $name = $value;
            } else {
                throw new Exception('No name supplied');
            }
        }

        if (array_key_exists($name, $this->constants)) {
            throw new Exception('A constant of the name (' . $name . ') does already exist.');
        }

        $this->constants[$name] = $value;
    }

    /**
     * Adds a variable to the class
     * Throws Exception if the variable does already exist
     *
     * @param PhpVariable $variable The variable object to add
     * @access public
     * @throws Exception If the variable name already exists
     */
    public function addVariable(PhpVariable $variable)
    {
        if ($this->variableExists($variable->getIdentifier())) {
            throw new Exception('A variable of the name (' . $variable->getIdentifier() . ') does already exist.');
        }

        $this->variables[$variable->getIdentifier()] = $variable;
    }

    /**
     * Adds a function to the class
     * Overwrites
     *
     * @param PhpFunction $function The function object to add
     * @access public
     * @throws Exception If the function name already exists
     */
    public function addFunction(PhpFunction $function)
    {
        if ($this->functionExists($function->getIdentifier())) {
            throw new Exception('A function of the name (' . $function->getIdentifier() . ') does already exist.');
        }

        $this->functions[$function->getIdentifier()] = $function;
    }

    /**
     * Checks if a variable with the same name does already exist
     *
     * @access public
     * @param string $identifier
     * @return bool
     */
    public function variableExists($identifier)
    {
        return array_key_exists($identifier, $this->variables);
    }

    /**
     * Checks if a function with the same name does already exist
     *
     * @access public
     * @param string $identifier
     * @return bool
     */
    public function functionExists($identifier)
    {
        return array_key_exists($identifier, $this->functions);
    }
}
