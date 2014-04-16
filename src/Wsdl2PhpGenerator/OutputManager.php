<?php

/**
 * @package Wsdl2PhpGenerator
 */
namespace Wsdl2PhpGenerator;

use \Exception;
use Wsdl2PhpGenerator\PhpSource\PhpClass;
use Wsdl2PhpGenerator\PhpSource\PhpFile;

/**
 * Manages the output of php files from the generator
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class OutputManager
{
    /**
     * @var string The directory to save the files
     */
    private $dir;

    /**
     * @var bool If we should add a namespace to the files
     */
    private $useNamespace;

    /**
     * @var array An array with classnames to save
     */
    private $classesToSave;

    /**
     * @var ConfigInterface A reference to the config
     */
    private $config;

    /**
     * @var PhpFile The file object to use for saving the files
     */
    private $file;

    /**
     * @param ConfigInterface $config The config to use
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
        $this->dir = '';
        $this->useNamespace = (strlen($this->config->get('namespaceName')) > 0);
        $this->classesToSave = $this->config->get('classNames');
        $this->file = null;
    }

    /**
     * Saves the service and types php code to file
     *
     * @param PhpClass $service
     * @param array $types
     */
    public function save(PhpClass $service, array $types)
    {
        $this->setOutputDirectory();

        if ($this->config->get('oneFile')) {
            if (!is_dir($this->config->get('outputDir'))) {
                $this->file = new PhpFile(basename($this->config->get('outputDir')));
            } else {
                $this->file = new PhpFile($service->getIdentifier());
            }
            $this->addNamespace();
            $this->addClassToFile($service);
            foreach ($types as $type) {
                $this->addClassToFile($type);
            }

            if (!is_dir($this->config->get('outputDir'))) {
                $this->file->save(dirname($this->config->get('outputDir')));
            } else {
                $this->file->save($this->dir);
            }
        } else {
            $this->saveClassToFile($service);
            foreach ($types as $type) {
                $this->saveClassToFile($type);
            }
        }
    }

    /**
     * Sets the output directory, creates it if needed
     * This must be called before saving a file
     *
     * @throws Exception If the dir can't be created and dont already exists
     */
    private function setOutputDirectory()
    {
        $outputDirectory = $this->config->get('outputDir');

        //Try to create output dir if non existing
        if ($this->config->get('oneFile')) {
            $outputDirectory = dirname($outputDirectory);
        }
        if (is_dir($outputDirectory) == false) {
            if (mkdir($outputDirectory, 0777, true) == false) {
                throw new Exception('Could not create output directory and it does not exist!');
            }
        }

        $this->dir = $outputDirectory;
    }

    /**
     * Append a class to a file
     * If no file is created the name of the class is the filename
     *
     * @param PhpClass $class
     */
    private function addClassToFile(PhpClass $class)
    {
        // Check if the class should be saved
        if ($this->isValidClass($class)) {
            if ($this->file == null) {
                $this->file = new PhpFile($class->getIdentifier());
                $this->addNamespace();
            }

            $this->file->addClass($class);
        }
    }

    /**
     * Append a class to a file and save it
     * If no file is created the name of the class is the filename
     *
     * @param PhpClass $class
     */
    private function saveClassToFile(PhpClass $class)
    {
        $this->addClassToFile($class);
        if ($this->file != null) {
            $this->file->save($this->dir);
            $this->file = null;
        }
    }

    /**
     * Checks if a namespace should be added, if so it adds it to the current file
     */
    private function addNamespace()
    {
        if ($this->useNamespace && $this->file->hasNamespace() == false) {
            $this->file->addNamespace($this->config->get('namespaceName'));
        }
    }

    /**
     * Checks if the class is approved
     * Removes the prefix and suffix for namechecking
     *
     * @param PhpClass $class
     * @return bool Returns true if the class is ok to add to file
     */
    private function isValidClass(PhpClass $class)
    {
        $suffix = strlen($this->config->get('suffix'));
        if ($suffix > 0) {
            $nSuf = 0 - $suffix;
            $className = substr($class->getIdentifier(), strlen($this->config->get('prefix')), $nSuf);
        } else {
            $className = substr($class->getIdentifier(), strlen($this->config->get('prefix')));
        }

        if (count($this->classesToSave) == 0 || count($this->classesToSave) > 0 && in_array($className, $this->classesToSave)) {
            return true;
        }

        return false;
    }
}
