<?php
/**
 * @package Wsdl2PhpGenerator
 */
namespace Wsdl2PhpGenerator;

/**
 * Class that contains functionality to validate a string as valid php
 * Contains functionf for validating Type, Classname and Naming convention
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Validator
{
    /**
     * @var array Array containing all keywords in php
     * @link http://www.php.net/manual/en/reserved.keywords.php
     */
    private static $keywords = array(
        '__halt_compiler',
        'abstract',
        'and',
        'array',
        'as',
        'break',
        'callable',
        'case',
        'catch',
        'class',
        'clone',
        'const',
        'continue',
        'declare',
        'default',
        'die',
        'do',
        'echo',
        'else',
        'elseif',
        'empty',
        'enddeclare',
        'endfor',
        'endforeach',
        'endif',
        'endswitch',
        'endwhile',
        'eval',
        'exit',
        'extends',
        'final',
        'finally',
        'for',
        'foreach',
        'function',
        'global',
        'goto',
        'if',
        'implements',
        'include',
        'include_once',
        'instanceof',
        'insteadof',
        'interface',
        'isset',
        'list',
        'namespace',
        'new',
        'or',
        'print',
        'private',
        'protected',
        'public',
        'require',
        'require_once',
        'return',
        'static',
        'switch',
        'throw',
        'trait',
        'try',
        'unset',
        'use',
        'var',
        'while',
        'xor',
        'yield'
    );

    /**
     * @var array Array containing primitive types
     */
    private static $primitives = array(
        'string',
        'int',
        'float',
        'double',
        'bool',
        'boolean'
    );

    /**
     * Changes the name if it is invalid to a valid name
     *
     * @param string $name The name to validate
     * @return string Returns the validated name
     */
    public static function validateClass($name)
    {
        return self::validateClassName($name);
    }

    public static function validateOperation($name) {
        return self::validateOperationName($name);
    }

    /**
     * @param string $name The name to validate
     * @return string The validated name
     */
    public static function validateType($name)
    {
        return self::validateTypeName($name);
    }

    /**
     * Validates a name against standard PHP naming conventions
     *
     * @param string $name the name to validate
     * @return string the validated version of the submitted name
     */
    public static function validateNamingConvention($name)
    {
        // Prepend the string a to names that begin with anything but a-z This is to make a valid name
        if (preg_match('/^[A-Za-z_]/', $name) == false) {
            $name = 'a' . $name;
        }

        return preg_replace('/[^a-zA-Z0-9_x7f-xff]*/', '', preg_replace('/^[^a-zA-Z_x7f-xff]*/', '', $name));
    }

    /**
     * Checks if $str is a primitive datatype
     *
     * @param string $str
     * @return bool True if $str is a primitive
     */
    public static function isPrimitive($str)
    {
        return in_array(strtolower($str), self::$primitives);
    }

    /**
     * Validates a class name against PHP naming conventions and already defined
     * classes, and optionally stores the class as a member of the interpreted classmap.
     *
     * @param string $className the name of the class to test
     * @return string The validated version of the submitted class name
     * @throws ValidationException
     */
    private static function validateClassName($className)
    {
        $validClassName = self::validateNamingConvention($className);

        if (class_exists($validClassName)) {
            throw new ValidationException("Class " . $validClassName . " already defined. Cannot redefine class with class loaded.");
        }

        if (self::isKeyword($validClassName)) {
            throw new ValidationException($validClassName . ' is a restricted keyword.');
        }

        return $validClassName;
    }

    private static function validateOperationName($operationName) {
        $operationName = self::validateNamingConvention($operationName);

        // Operations cannot be called the same as restricted keywords. This results in syntax errors when loading the
        // generated code.
        if (self::isKeyword($operationName)) {
            throw new ValidationException($operationName . ' is a restricted keyword.');
        }

        return $operationName;
    }

    /**
     * Validates a wsdl type against known PHP primitive types, or otherwise
     * validates the namespace of the type to PHP naming conventions
     *
     * @param string $type the type to test
     * @return string the validated version of the submitted type
     * @throws ValidationException
     */
    private static function validateTypeName($type)
    {
        if (substr($type, -2) == "[]") {
            return $type;
        }
        if (strtolower(substr($type, 0, 7)) == "arrayof") {
            return substr($type, 7) . '[]';
        }

        switch (strtolower($type)) {
            case "int":
            case "integer":
            case "long":
            case "byte":
            case "short":
            case "negativeinteger":
            case "nonnegativeinteger":
            case "nonpositiveinteger":
            case "positiveinteger":
            case "unsignedbyte":
            case "unsignedint":
            case "unsignedlong":
            case "unsignedshort":
                return 'int';
                break;
            case "float":
            case "double":
            case "decimal":
                return 'float';
                break;
            case "<anyxml>":
            case "string":
            case "token":
            case "normalizedstring":
            case "hexbinary":
                return 'string';
                break;
            default:
                $validType = self::validateNamingConvention($type);
                break;
        }

        if (self::isKeyword($validType)) {
            throw new ValidationException($validType . ' is a restricted keyword.');
        }

        return $validType;
    }

    /**
     * Checks if $str is a restricted keyword
     *
     * @param string $str
     * @return bool True if $str is a restricted keyword
     */
    public static function isKeyword($str)
    {
        return in_array(strtolower($str), self::$keywords);
    }
}
