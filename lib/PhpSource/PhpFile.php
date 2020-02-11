<?php
/**
 * @package phpSource
 */
namespace Wsdl2PhpGenerator\PhpSource;

use Exception;
use Wsdl2PhpGenerator\PhpSource\PhpDocComment;

/**
 * Class that represents the source code for a php file
 * A file can contain namespaces, classes and global functions
 *
 * @package phpSource
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class PhpFile
{
    /**
     *
     * @var string The filename of the file
     * @access private
     */
    private $name;

    /**
     *
     * @var array Array of strings, the strings are namespace names, only one namespace supported for now
     * @access private
     */
    private $namespaces;

    /**
     *
     * @var array Array of PhpClass objects
     * @access private
     */
    private $classes;

    /**
     *
     * @var array Array of PhpFunction objects
     * @access private
     */
    private $functions;

    /**
     *
     * @var array Array of strings, the strings are disclaimers
     * @access private
     */
    private $disclaimers;

    /**
     * Sets the name of the file, and sets all other members to empty
     *
     * @param string $name The name of the file
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->namespaces = array();
        $this->classes = array();
        $this->functions = array();
    }

    /**
     * Generates the complete source code for the file
     *
     * @return string The source code for the file
     */
    public function getSource()
    {
        $ret = '<?php';

        if ($this->hasDisclaimer()) {
            foreach ($this->disclaimers as $disclaimer) {
                $ret .= $disclaimer->getSource(). PHP_EOL;
            }
        } else {
            $ret .= PHP_EOL . PHP_EOL;
        }

        $namespace = $classNamespace = null;
        if ($this->hasNamespace()) {
            $namespace = $this->namespaces[0];
        }

        $classesSource = '';
        if (count($this->classes) > 0) {
            foreach ($this->classes as $class) {
                if (!$classNamespace && $class->getClassNamespace()) {
                    $classNamespace = $class->getClassNamespace();
                }
                $classesSource .= $class->getSource();
            }
        }

        $namespace = $classNamespace ?: $namespace;
        if ($namespace) {
            $ret .= 'namespace ' . $namespace . ';' . PHP_EOL . PHP_EOL;
        }
        $ret .= $classesSource;

        if (count($this->functions) > 0) {
            foreach ($this->functions as $function) {
                $ret .= $function->getSource();
            }
        }

        return $ret;
    }

    /**
     * Saves the source code for the file in $directory
     *
     * @param string $directory Should be / terminated and writeable
     */
    public function save($directory)
    {
        file_put_contents($directory . DIRECTORY_SEPARATOR . $this->name . '.php', $this->getSource());
    }

    /**
     * Adds a namespace, only one namespace is currently supported
     *
     * @param string $namespace The namespace to add
     */
    public function addNamespace($namespace)
    {
        if (in_array($namespace, $this->namespaces) == false) {
            $this->namespaces[] = $namespace;
        }
    }

    /**
     * Checks if the file has a namespace
     *
     * @return bool Returns true if a namespace is added to the file
     */
    public function hasNamespace()
    {
        return (count($this->namespaces) > 0);
    }

    /**
     * Adds a disclaimer
     *
     * @param string $disclaimer The disclaimer to add
     */
    public function addDisclaimer($disclaimer)
    {
        if (!$disclaimer) {
            return;
        }
        $key = md5($disclaimer);
        if (!isset($this->disclaimers[$key])) {
            $this->disclaimers[$key] = new PhpDocComment($disclaimer);
        }
    }

    /**
     * Checks if the file has a disclaimer
     *
     * @return bool Returns true if a disclaimer is added to the file
     */
    public function hasDisclaimer()
    {
        return (count($this->disclaimers) > 0);
    }

    /**
     * Adds a class to the file
     *
     * @param PhpClass $class The class to add
     * @throws Exception If the class already exists
     */
    public function addClass(PhpClass $class)
    {
        if ($this->classExists($class->getIdentifier())) {
            throw new Exception('A class of the name (' . $class->getIdentifier() . ') does already exist.');
        }

        $this->classes[$class->getIdentifier()] = $class;
    }

    /**
     * Adds a global function to the file, should not be used, classes rocks :)
     *
     * @param PhpFunction $function The function to add
     * @throws Exception If the function already exists
     */
    public function addFunction(PhpFunction $function)
    {
        if ($this->functionExists($function->getIdentifier())) {
            throw new Exception('A function of the name (' . $function->getIdentifier() . ') does already exist.');
        }

        $this->functions[$function->getIdentifier()] = $function;
    }

    /**
     * Checks if a class with the same name does already exist
     *
     * @access public
     * @param string $identifier
     * @return bool
     */
    public function classExists($identifier)
    {
        return array_key_exists($identifier, $this->classes);
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
