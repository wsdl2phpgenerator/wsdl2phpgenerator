<?php
/**
 * @package Wsdl2PhpGenerator
 */

namespace wsdl2php;

/**
 * Include the needed files
 */
require_once \dirname(__FILE__).'/Exception.php';

/**
 * Wrapper class for exception, only use is to collect functionality in one namespace
 * This groups all validation exeptions to one class
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik@wallgren.me>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @see \wsdl2php\Exception
 * @see \wsdl2php\Validator
 */
class ValidationException extends \wsdl2php\Exception
{

}