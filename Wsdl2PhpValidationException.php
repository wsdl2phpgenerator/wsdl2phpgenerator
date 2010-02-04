<?php

namespace Wsdl2Php;

include_once('Wsdl2PhpException.php');

/**
 * Wrapper class for exception, only use is to collect functionality in one namespace
 * This groups all validation exeptions to one class
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik@wallgren.me>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @see Exception
 * @see Validator
 */
class ValidationException extends Exception
{

}