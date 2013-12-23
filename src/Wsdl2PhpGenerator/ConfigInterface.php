<?php
namespace Wsdl2PhpGenerator;

/**
 * Common interface for classes that contains all the settings possible for the Wsdl2PhpGenerator.
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
interface ConfigInterface
{
    /**
     *
     * @return bool Returns true if no type constructor should be used
     * @access public
     */
    public function getNoTypeConstructor();

    /**
     * @return bool Returns if the output should be protected with class_exists statements
     * @access public
     */
    public function getClassExists();

    /**
     * @return string Returns the namespace name the output should use
     * @access public
     */
    public function getNamespaceName();

    /**
     * @return string Returns the path of the input file
     * @access public
     */
    public function getInputFile();

    /**
     * @return string Returns the path of the output directory
     * @access public
     */
    public function getOutputDir();

    /**
     *
     * @return array Returns an array with all classnames to generate
     */
    public function getClassNamesArray();

    /**
     *
     * @return string The list of classes
     */
    public function getClassNames();

    /**
     * @param boolean $oneFile
     */
    public function setOneFile($oneFile);

    /**
     *
     * @return string Returns whether include statements should be generated
     */
    public function getNoIncludes();

    /**
     *
     * @return string Returns the suffix if any
     */
    public function getSuffix();

    /**
     *
     * @return string The compression value to use for the client
     */
    public function getCompression();

    /**
     *
     * @return bool Returns true if verbose output is selected
     */
    public function getVerbose();

    /**
     *
     * @return array An array of strings of all the features to enable
     */
    public function getOptionFeatures();

    /**
     *
     * @return string Returns the prefix if any
     */
    public function getPrefix();

    /**
     * @param boolean $noTypeConstructor
     */
    public function setNoTypeConstructor($noTypeConstructor);

    /**
     * @param string $sharedTypes
     */
    public function setSharedTypes($sharedTypes);

    /**
     * @param boolean $createAccessors
     */
    public function setCreateAccessors($createAccessors);

    /**
     * @param string $compression
     */
    public function setCompression($compression);

    /**
     * @param boolean $constructorParamsDefaultToNull
     */
    public function setConstructorParamsDefaultToNull($constructorParamsDefaultToNull);

    /**
     * @return boolean Returns if getter and setter methods should be created for member variables
     */
    public function getCreateAccessors();

    /**
     *
     * @return string Returns the string with the constant to use for wsdl cache
     */
    public function getWsdlCache();

    /**
     * @param string $namespaceName
     */
    public function setNamespaceName($namespaceName);

    /**
     * @return bool Returns if the output should be gathered to one file
     * @access public
     */
    public function getOneFile();

    /**
     * @param boolean $verbose
     */
    public function setVerbose($verbose);

    /**
     * @return boolean Returns if the constructor parameters should have null default values
     */
    public function getConstructorParamsDefaultToNull();

	/**
	 * @return string Returns the username that should be used for HTTP Basic Authentication.
	 */
	public function getLogin();

	/**
	 * @return string Returns the password that should be used for HTTP Basic Authentication.
	 */
	public function getPassword();
}
