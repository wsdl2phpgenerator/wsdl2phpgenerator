<?php
/**
 * @package Wsdl2PhpGenerator
 */

/**
 * Class that contains all the settings possible for the Wsdl2PhpGenerator
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Config
{
    /**
     *
     * @var string The name to use as namespace in the new classes, no namespaces is used if empty
     * @access private
     */
    private $namespaceName;

    /**
     *
     * @var bool Descides if the output is collected to one file or spread over one file per class
     * @access private
     */
    private $oneFile;

    /**
     *
     * @var bool Decides if the output should protect all classes with if(!class_exists statements
     * @access private
     */
    private $classExists;

    /**
     *
     * @var string The directory where to put the file(s)
     * @access private
     */
    private $outputDir;

    /**
     *
     * @var string The wsdl file to use to generate the classes
     * @access private
     */
    private $inputFile;

    /**
     * The array should contain the strings for the options to enable
     *
     * @var array containing all features in the options for the client
     */
    private $optionFeatures;

    /**
     *
     * @var string The wsdl cache to use if any. Possible values WSDL_CACHE_NONE, WSDL_CACHE_DISK, WSDL_CACHE_MEMORY or WSDL_CACHE_BOTH
     */
    private $wsdlCache;

    /**
     *
     * @var string The compression string to use
     */
    private $compression;

    /**
     *
     * @var string A comma separated list of classes to generate. Used to specify the classes to generate if the user doesn't want to generate all
     */
    private $classNames;

    /**
     *
     * @var bool If a type constructor should not be generated
     */
    private $noTypeConstructor;

    /**
     *
     * @var bool If we should output verbose information
     */
    private $verbose;

    /**
     *
     * @var string The prefix to use for all classes
     */
    private $prefix;

    /**
     *
     * @var string The sufix to use for all classes
     */
    private $suffix;

    /**
     *
     * @var string If multiple class got the name, the first will be used, other will be ignored
     */
    private $sharedTypes;

    /**
     *
     * @var bool Decides if getter and setter methods should be created for member variables
     * @access private
     */
    private $createAccessors;

    /**
     *
     * @var bool Decides if the constructor parameters should have null default values
     * @access private
     */
    private $constructorParamsDefaultToNull;

    /**
     * @var bool If the service class should explicitly include all other classes or not
     *  Including may not be needed if using an autoloader etc.
     */
    private $noIncludes;

    /**
     * Sets all variables
     *
     * @param string $inputFile
     * @param string $outputDir
     * @param bool $verbose
     * @param bool $oneFile
     * @param bool $classExists
     * @param bool $noTypeConstructor
     * @param string $namespaceName
     * @param array $optionsFeatures
     * @param string $wsdlCache
     * @param string $compression
     * @param string $classNames
     * @param string $prefix
     * @param string $suffix
     * @param string $sharedTypes
     * @param bool $createAccessors
     * @param bool $constructorParamsDefaultToNull
     * @param bool $noIncludes
     */
    public function __construct($inputFile, $outputDir, $verbose = false, $oneFile = false, $classExists = false, $noTypeConstructor = false, $namespaceName = '', $optionsFeatures = array(), $wsdlCache = '', $compression = '', $classNames = '', $prefix = '', $suffix = '', $sharedTypes = false, $createAccessors = false, $constructorParamsDefaultToNull = false, $noIncludes = false)
    {
        $this->namespaceName = trim($namespaceName);
        $this->oneFile = $oneFile;
        $this->verbose = $verbose;
        $this->classExists = $classExists;
        $this->noTypeConstructor = $noTypeConstructor;
        $this->outputDir = trim($outputDir);
        if (is_array($inputFile)) {
            foreach ($inputFile as &$file) {
                $file = trim($file);
            }
        } else {
            $inputFile = trim($inputFile);
        }
        $this->inputFile = $inputFile;
        $this->optionFeatures = $optionsFeatures;
        $this->wsdlCache = '';
        if (in_array($wsdlCache, array('WSDL_CACHE_NONE', 'WSDL_CACHE_DISK', 'WSDL_CACHE_MEMORY', 'WSDL_CACHE_BOTH'))) {
            $this->wsdlCache = $wsdlCache;
        }
        $this->compression = trim($compression);
        $this->classNames = trim($classNames);
        $this->prefix = trim($prefix);
        $this->suffix = trim($suffix);
        $this->sharedTypes = trim($sharedTypes);
        $this->createAccessors = $createAccessors;
        $this->constructorParamsDefaultToNull = $constructorParamsDefaultToNull;
        $this->noIncludes = $noIncludes;
    }

    /**
     * @return string Returns the namespace name the output should use
     * @access public
     */
    public function getNamespaceName()
    {
        return $this->namespaceName;
    }

    /**
     * @return bool Returns if the output should be gathered to one file
     * @access public
     */
    public function getOneFile()
    {
        return $this->oneFile;
    }

    /**
     * @return bool Returns if the output should be protected with class_exists statements
     * @access public
     */
    public function getClassExists()
    {
        return $this->classExists;
    }

    /**
     *
     * @return bool Returns true if no type constructor should be used
     * @access public
     */
    public function getNoTypeConstructor()
    {
        return $this->noTypeConstructor;
    }

    /**
     * @return string Returns the path of the output directory
     * @access public
     */
    public function getOutputDir()
    {
        return $this->outputDir;
    }

    /**
     * @return string Returns the path of the input file
     * @access public
     */
    public function getInputFile()
    {
        return $this->inputFile;
    }

    /**
     *
     * @return array An array of strings of all the features to enable
     */
    public function getOptionFeatures()
    {
        return $this->optionFeatures;
    }

    /**
     *
     * @return string Returns the string with the constant to use for wsdl cache
     */
    public function getWsdlCache()
    {
        return $this->wsdlCache;
    }

    /**
     *
     * @return string The compression value to use for the client
     */
    public function getCompression()
    {
        return $this->compression;
    }

    /**
     *
     * @return string The list of classes
     */
    public function getClassNames()
    {
        return $this->classNames;
    }

    /**
     *
     * @return array Returns an array with all classnames to generate
     */
    public function getClassNamesArray()
    {
        if (strpos($this->classNames, ',') !== false) {
            return array_map('trim', explode(',', $this->classNames));
        } elseif (strlen($this->classNames) > 0) {
            return array($this->classNames);
        }

        return array();
    }

    /**
     *
     * @return bool Returns true if verbose output is selected
     */
    public function getVerbose()
    {
        return $this->verbose;
    }

    /**
     *
     * @return string Returns the prefix if any
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     *
     * @return string Returns the suffix if any
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     *
     * @return string Returns the shared types
     */
    public function getSharedTypes()
    {
        return $this->sharedTypes;
    }

    /**
     * @return boolean Returns if getter and setter methods should be created for member variables
     */
    public function getCreateAccessors()
    {
        return $this->createAccessors;
    }

    /**
     * @return boolean Returns if the constructor parameters should have null default values
     */
    public function getConstructorParamsDefaultToNull()
    {
        return $this->constructorParamsDefaultToNull;
    }

    /**
     *
     * @return string Returns whether include statements should be generated
     */
    public function getNoIncludes()
    {
        return $this->noIncludes;
    }

    /**
     * @param boolean $classExists
     */
    public function setClassExists($classExists)
    {
        $this->classExists = $classExists;
    }

    /**
     * @param string $classNames
     */
    public function setClassNames($classNames)
    {
        $this->classNames = $classNames;
    }

    /**
     * @param string $compression
     */
    public function setCompression($compression)
    {
        $this->compression = $compression;
    }

    /**
     * @param string $inputFile
     */
    public function setInputFile($inputFile)
    {
        $this->inputFile = $inputFile;
    }

    /**
     * @param string $namespaceName
     */
    public function setNamespaceName($namespaceName)
    {
        $this->namespaceName = $namespaceName;
    }

    /**
     * @param boolean $noTypeConstructor
     */
    public function setNoTypeConstructor($noTypeConstructor)
    {
        $this->noTypeConstructor = $noTypeConstructor;
    }

    /**
     * @param boolean $oneFile
     */
    public function setOneFile($oneFile)
    {
        $this->oneFile = $oneFile;
    }

    /**
     * @param array $optionFeatures
     */
    public function setOptionFeatures($optionFeatures)
    {
        $this->optionFeatures = $optionFeatures;
    }

    /**
     * @param string $outputDir
     */
    public function setOutputDir($outputDir)
    {
        $this->outputDir = $outputDir;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @param string $sharedTypes
     */
    public function setSharedTypes($sharedTypes)
    {
        $this->sharedTypes = $sharedTypes;
    }

    /**
     * @param string $suffix
     */
    public function setSuffix($suffix)
    {
        $this->suffix = $suffix;
    }

    /**
     * @param boolean $verbose
     */
    public function setVerbose($verbose)
    {
        $this->verbose = $verbose;
    }

    /**
     * @param string $wsdlCache
     */
    public function setWsdlCache($wsdlCache)
    {
        $this->wsdlCache = $wsdlCache;
    }

    /**
     * @param boolean $createAccessors
     */
    public function setCreateAccessors($createAccessors)
    {
        $this->createAccessors = $createAccessors;
    }

    /**
     * @param boolean $constructorParamsDefaultToNull
     */
    public function setConstructorParamsDefaultToNull($constructorParamsDefaultToNull)
    {
        $this->constructorParamsDefaultToNull = $constructorParamsDefaultToNull;
    }

    /**
     * @param boolean $noIncludes
     */
    public function setNoIncludes($noIncludes)
    {
        $this->noIncludes = $noIncludes;
    }
}
