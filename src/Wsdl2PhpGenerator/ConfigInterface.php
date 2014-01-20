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
     * Returns true if no type constructor should be used
     *
     * @return bool
     */
    public function getNoTypeConstructor();

    /**
     * Returns if the output should be protected with class_exists statements
     *
     * @return bool
     */
    public function getClassExists();

    /**
     * Returns the namespace name the output should use
     *
     * @return string
     */
    public function getNamespaceName();

    /**
     * Returns the path of the input file
     *
     * @return string
     */
    public function getInputFile();

    /**
     * Returns the path of the output directory
     *
     * @return string
     */
    public function getOutputDir();

    /**
     * Returns an array with all classnames to generate
     *
     * @return array
     */
    public function getClassNamesArray();

    /**
     * The list of classes
     *
     * @return string
     */
    public function getClassNames();

    /**
     * @param boolean $oneFile
     */
    public function setOneFile($oneFile);

    /**
     * Returns whether include statements should be generated
     *
     * @return string
     */
    public function getNoIncludes();

    /**
     * Returns the suffix if any
     *
     * @return string
     */
    public function getSuffix();

    /**
     * The compression value to use for the client
     *
     * @return string
     */
    public function getCompression();

    /**
     * Returns true if verbose output is selected
     *
     * @return bool
     */
    public function getVerbose();

    /**
     * An array of strings of all the features to enable
     *
     * @return array
     */
    public function getOptionFeatures();

    /**
     * Returns the prefix if any
     *
     * @return string
     */
    public function getPrefix();

    /**
     * @param boolean $noTypeConstructor
     */
    public function setNoTypeConstructor($noTypeConstructor);

    /**
     * @param bool $sharedTypes
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
     * Returns if getter and setter methods should be created for member variables
     *
     * @return boolean
     */
    public function getCreateAccessors();

    /**
     * Returns the string with the constant to use for wsdl cache
     *
     * @return string
     */
    public function getWsdlCache();

    /**
     * @param string $namespaceName
     */
    public function setNamespaceName($namespaceName);

    /**
     * Returns if the output should be gathered to one file
     *
     * @return bool
     */
    public function getOneFile();

    /**
     * @param boolean $verbose
     */
    public function setVerbose($verbose);

    /**
     * Returns if the constructor parameters should have null default values
     *
     * @return boolean
     */
    public function getConstructorParamsDefaultToNull();
}
