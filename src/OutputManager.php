<?php

/**
 * @package Wsdl2PhpGenerator
 */

/**
 * @see phpSourcePhpClass
 */
require_once dirname(__FILE__).'/../lib/phpSource/PhpFile.php';

/**
 * Manages the output of php files from the generator
 *
 * @package Wsdl2PhpGenerator
 * @author Fredrik Wallgren <fredrik@wallgren.me>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class wsdl2phpOutputManager
{
  /**
   *
   * @var string The directory to save the files
   */
  private $dir;
  
  /**
   *
   * @var bool If we should add a namespace to the files
   */
  private $useNamespace;
  
  /**
   *
   * @var array An array with classnames to save
   */
  private $classesToSave;
  
  /**
   *
   * @var wsdl2phpConfig A reference to the config 
   */
  private $config;
  
  /**
   *
   * @var phpSourcePhpFile The file object to use for saving the files
   */
  private $file;
  
  /**
   *
   * @param Config $config The config to use
   */
  public function __construct(wsdl2phpConfig $config)
  {
    $this->config = $config;
    $this->dir = '';
    $this->useNamespace = (strlen($this->config->getNamespaceName()) > 0);
    $this->classesToSave = $this->config->getClassNamesArray();
    $this->file = null;
  }

  /**
   * Saves the service and types php code to file
   *
   * @param PhpClass $service
   * @param array $types
   */
  public function save(phpSourcePhpClass $service, array $types)
  {
    $this->setOutputDirectory();
    
    if ($this->config->getOneFile())
    {
      $this->file = new phpSourcePhpFile($service->getIdentifier());
      $this->addNamespace();
      $this->addClassToFile($service);
      foreach ($types as $type)
      {
        $this->addClassToFile($type);
      }
      
      $this->file->save($this->dir);
    }
    else
    {
      $this->saveClassToFile($service);
      foreach ($types as $type)
      {
        $this->saveClassToFile($type);
      }
    }
  }

  /**
   * Sets the output directory, creates it if needed
   * This must be called before saving a file
   *
   * @throws wsdl2phpException If the dir can't be created and dont already exists
   */
  private function setOutputDirectory()
  {
    $outputDirectory = $this->config->getOutputDir();
    
    //Try to create output dir if non existing
    if (is_dir($outputDirectory) == false && is_file($outputDirectory) == false)
    {
      if(mkdir($outputDirectory, 0777, true) == false)
      {
        throw new wsdl2phpException('Could not create output directory and it does not exist!');
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
  private function addClassToFile(phpSourcePhpClass $class)
  {
    // Check if the class should be saved
    if ($this->isValidClass($class))
    {
      if ($this->file == null)
      {
        $this->file = new phpSourcePhpFile($class->getIdentifier());
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
  private function saveClassToFile(phpSourcePhpClass $class)
  {
    $this->addClassToFile($class);
    if ($this->file != null)
    {
      $this->file->save($this->dir);
      $this->file = null;
    }
  }

  /**
   * Checks if a namespace should be added, if so it adds it to the current file
   */
  private function addNamespace()
  {
    if ($this->useNamespace && $this->file->hasNamespace() == false)
    {
      $this->file->addNamespace($this->config->getNamespaceName());
    }
  }

  /**
   * Checks if the class is approved
   * Removes the prefix and suffix for namechecking
   *
   * @param PhpClass $class
   * @return bool Returns true if the class is ok to add to file
   */
  private function isValidClass(phpSourcePhpClass $class)
  {
    $suffix = strlen($this->config->getSuffix());
    if ($suffix > 0)
    {
      $nSuf = 0 - $suffix;
      $className = substr($class->getIdentifier(), strlen($this->config->getPrefix()), $nSuf);
    }
    else
    {
      $className = substr($class->getIdentifier(), strlen($this->config->getPrefix()));
    }

    if (count($this->classesToSave) == 0 || count($this->classesToSave) > 0 && in_array($className, $this->classesToSave))
    {
      return true;
    }
    
    return false;
  }
}