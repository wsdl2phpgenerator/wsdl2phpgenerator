<?php
/**
 * @package Wsdl2PhpGenerator
 */
namespace Wsdl2PhpGenerator;

class Config implements ConfigInterface
{
    /**
     * The name to use as namespace in the new classes, no namespaces is used if empty
     *
     * @var string
     */
    private $namespaceName;

    /**
     * Decides if the output is collected to one file or spread over one file per class
     *
     * @var bool
     */
    private $oneFile;

    /**
     * Decides if the output should protect all classes with if(!class_exists statements
     *
     * @var bool
     */
    private $classExists;

    /**
     * The directory where to put the file(s)
     *
     * @var string
     */
    private $outputDir;

    /**
     * The wsdl file to use to generate the classes
     *
     * @var string
     */
    private $inputFile;

    /**
     * The array should contain the strings for the options to enable
     *
     * @var array
     */
    private $optionFeatures;

    /**
     * The wsdl cache to use if any.
     * Possible values WSDL_CACHE_NONE, WSDL_CACHE_DISK, WSDL_CACHE_MEMORY or WSDL_CACHE_BOTH
     *
     * @var string
     */
    private $wsdlCache;

    /**
     * The compression string to use
     *
     * @var string
     */
    private $compression;

    /**
     * A comma separated list of classes to generate.
     * Used to specify the classes to generate if the user doesn't want to generate all
     *
     * @var string
     */
    private $classNames;

    /**
     * If a type constructor should not be generated
     *
     * @var bool
     */
    private $noTypeConstructor;

    /**
     *
     * @var bool If we should output verbose information
     */
    private $verbose;

    /**
     * The prefix to use for all classes
     *
     * @var string
     */
    private $prefix;

    /**
     * The suffix to use for all classes
     *
     * @var string
     */
    private $suffix;

    /**
     * If multiple class got the name, the first will be used, other will be ignored
     *
     * @var string
     */
    private $sharedTypes;

    /**
     * Decides if getter and setter methods should be created for member variables
     *
     * @var bool
     */
    private $createAccessors;

    /**
     * Decides if the constructor parameters should have null default values
     *
     * @var bool
     */
    private $constructorParamsDefaultToNull;

    /**
     * If the service class should explicitly include all other classes or not
     * Including may not be needed if using an autoloader etc.
     *
     * @var bool
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
     * @param bool $sharedTypes
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
        $this->sharedTypes = $sharedTypes;
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

    /**
     * @return array|string
     */
    public function getInputFile()
    {
        return $this->inputFile;
    }

    /**
     * @return array
     */
    public function getOptionFeatures()
    {
        return $this->optionFeatures;
    }

    /**
     * @return string
     */
    public function getWsdlCache()
    {
        return $this->wsdlCache;
    }

    /**
     * @return string
     */
    public function getCompression()
    {
        return $this->compression;
    }

    /**
     * @return string
     */
    public function getClassNames()
    {
        return $this->classNames;
    }

    /**
     * @return array
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
     * @return bool
     */
    public function getVerbose()
    {
        return $this->verbose;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        return $this->suffix;
    }

    /**
     * @return string
     */
    public function getSharedTypes()
    {
        return $this->sharedTypes;
    }

    /**
     * @return bool
     */
    public function getCreateAccessors()
    {
        return $this->createAccessors;
    }

    /**
     * @return bool
     */
    public function getConstructorParamsDefaultToNull()
    {
        return $this->constructorParamsDefaultToNull;
    }

    /**
     * @return bool|string
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
     * @param bool $noTypeConstructor
     */
    public function setNoTypeConstructor($noTypeConstructor)
    {
        $this->noTypeConstructor = $noTypeConstructor;
    }

    /**
     * @param bool $oneFile
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
     * @param bool $sharedTypes
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
     * @param bool $verbose
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
     * @param bool $createAccessors
     */
    public function setCreateAccessors($createAccessors)
    {
        $this->createAccessors = $createAccessors;
    }

    /**
     * @param bool $constructorParamsDefaultToNull
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
