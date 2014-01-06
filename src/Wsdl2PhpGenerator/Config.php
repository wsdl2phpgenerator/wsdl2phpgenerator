<?php
/**
 * @package Wsdl2PhpGenerator
 */
namespace Wsdl2PhpGenerator;

class Config implements ConfigInterface
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
    public function __construct($inputFile, $outputDir, $verbose = false, $oneFile = false, $classExists = false, $noTypeConstructor = false, $namespaceName = '', $optionsFeatures = array(), $wsdlCache = '', $compression = '', $classNames = '', $prefix = '', $suffix = '', $sharedTypes = null, $createAccessors = false, $constructorParamsDefaultToNull = false, $noIncludes = false)
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

    public function getNamespaceName()
    {
        return $this->namespaceName;
    }

    public function getOneFile()
    {
        return $this->oneFile;
    }

    public function getClassExists()
    {
        return $this->classExists;
    }

    public function getNoTypeConstructor()
    {
        return $this->noTypeConstructor;
    }

    public function getOutputDir()
    {
        return $this->outputDir;
    }

    public function getInputFile()
    {
        return $this->inputFile;
    }

    public function getOptionFeatures()
    {
        return $this->optionFeatures;
    }

    public function getWsdlCache()
    {
        return $this->wsdlCache;
    }

    public function getCompression()
    {
        return $this->compression;
    }

    public function getClassNames()
    {
        return $this->classNames;
    }

    public function getClassNamesArray()
    {
        if (strpos($this->classNames, ',') !== false) {
            return array_map('trim', explode(',', $this->classNames));
        } elseif (strlen($this->classNames) > 0) {
            return array($this->classNames);
        }

        return array();
    }

    public function getVerbose()
    {
        return $this->verbose;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

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

    public function getCreateAccessors()
    {
        return $this->createAccessors;
    }

    public function getConstructorParamsDefaultToNull()
    {
        return $this->constructorParamsDefaultToNull;
    }

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

    public function setNamespaceName($namespaceName)
    {
        $this->namespaceName = $namespaceName;
    }

    public function setNoTypeConstructor($noTypeConstructor)
    {
        $this->noTypeConstructor = $noTypeConstructor;
    }

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

    public function setCreateAccessors($createAccessors)
    {
        $this->createAccessors = $createAccessors;
    }

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
