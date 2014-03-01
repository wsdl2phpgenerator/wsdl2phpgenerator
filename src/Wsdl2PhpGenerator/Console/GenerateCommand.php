<?php


namespace Wsdl2PhpGenerator\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Wsdl2PhpGenerator\Config;
use Wsdl2PhpGenerator\GeneratorInterface;

/**
 * The console command which generates PHP code from a WSDL file.
 * This maps input arguments and options to a configuration and launches the generator.
 *
 * @package Wsdl2PhpGenerator\Console
 */
class GenerateCommand extends Command
{

    /**
     * The generator to be used when executing the command.
     *
     * @var GeneratorInterface
     */
    protected $generator;

    /**
     * An array of functions which map input arguments and options to configuration options.
     *
     * @var array
     */
    protected $inputConfigMapping = array();

    protected function configure()
    {
        $this
            ->setName('wsdl2phpgenerator:generate')
            ->setAliases(array('generate'))
            ->setDescription('Generate PHP classes from a WSDL file')
            // Input and output configuration is required and should thus be arguments but to retain the signature of
            // previous versions we specify them as required options for now.
            ->addConfigOption(
                'input',
                'i',
                InputOption::VALUE_REQUIRED,
                'The input wsdl file <fg=yellow>*Required</fg=yellow>',
                null,
                'inputFile'
            )
            ->addConfigOption(
                'output',
                'o',
                InputOption::VALUE_REQUIRED,
                'The output directory or file if -s is used (in that case, .php will be appened to file name) <fg=yellow>*Required</fg=yellow>',
                null,
                'outputDir'
            )

            ->addConfigOption(
                'classes',
                'c',
                InputOption::VALUE_REQUIRED,
                "A comma separated list of classnames to generate.\nIf this is used only classes that exist in the list will be generated.\nIf the service is not in this list and the -s flag is used\nthe filename will be the name of the first class that is generated",
                null,
                'classNames'
            )
            ->addConfigOption(
                'classExists',
                'e',
                InputOption::VALUE_NONE,
                'If all classes should be guarded with if(!class_exists) statements',
                null,
                'classExists'
            )
            ->addConfigOption(
                'createAccessors',
                null,
                InputOption::VALUE_NONE,
                'Create getter and setter methods for member variables',
                null,
                'createAccessors'
            )
            ->addConfigOption(
                'constructorNull',
                null,
                InputOption::VALUE_NONE,
                'Set the default value for constructor parameters to null',
                null,
                'constructorParamsDefaultToNull'
            )
            ->addConfigOption(
                'gzip',
                null,
                InputOption::VALUE_NONE,
                'Adds the option to compress the wsdl with gzip to the client',
                null,
                'compression'
            )
            ->addConfigOption(
                'namespace',
                'n',
                InputOption::VALUE_REQUIRED,
                'Use namespace with the name',
                null,
                'namespaceName'
            )
            ->addConfigOption(
                'noIncludes',
                null,
                InputOption::VALUE_NONE,
                'Do not add include_once statements for loading individual files',
                null,
                'noIncludes'
            )
            ->addConfigOption(
                'noTypeConstructor',
                't',
                InputOption::VALUE_NONE,
                'If no type constructor should be generated',
                null,
                'noTypeConstructor'
            )
            ->addConfigOption(
                'prefix',
                'p',
                InputOption::VALUE_REQUIRED,
                'The prefix to use for the generated classes',
                null,
                'prefix'
            )
            ->addConfigOption(
                'sharedTypes',
                null,
                InputOption::VALUE_NONE,
                'If multiple class got the name, the first will be used, other will be ignored',
                null,
                'sharedTypes'
            )
            ->addConfigOption(
                'singleFile',
                's',
                InputOption::VALUE_NONE,
                'If the output should be a single file',
                null,
                'oneFile'
            )
            ->addConfigOption(
                'suffix',
                'q',
                InputOption::VALUE_REQUIRED,
                'The suffix to use for the generated classes',
                null,
                'suffix'
            )

            ->addCacheOption(
                'cacheNone',
                null,
                InputOption::VALUE_NONE,
                'Adds the option to not cache the wsdl to the client',
                null,
                'WSDL_CACHE_NONE'
            )
            ->addCacheOption(
                'cacheDisk',
                null,
                InputOption::VALUE_NONE,
                'Adds the option to cache the wsdl on disk to the client',
                null,
                'WSDL_CACHE_DISK'
            )
            ->addCacheOption(
                'cacheMemory',
                null,
                InputOption::VALUE_NONE,
                'Adds the option to cache the wsdl in memory to the client',
                null,
                'WSDL_CACHE_MEMORY'
            )
            ->addCacheOption(
                'cacheBoth',
                null,
                InputOption::VALUE_NONE,
                'Adds the option to cache the wsdl in memory and on disk to the client',
                null,
                'WSDL_CACHE_BOTH'
            )

            ->addFeatureOption(
                'singleElementArrays',
                null,
                InputOption::VALUE_NONE,
                'Adds the option to use single element arrays to the client',
                null,
                'SOAP_SINGLE_ELEMENT_ARRAYS'
            )
            ->addFeatureOption(
                'waitOneWayCalls',
                null,
                InputOption::VALUE_NONE,
                'Adds the option to use wait one way calls to the client',
                null,
                'SOAP_WAIT_ONE_WAY_CALLS'
            )
            ->addFeatureOption(
                'xsiArrayType',
                null,
                InputOption::VALUE_NONE,
                'Adds the option to use xsi arrays to the client',
                null,
                'SOAP_USE_XSI_ARRAY_TYPE'
            );
    }

    /**
     * @param GeneratorInterface $generator The generator to be used when executing the command.
     */
    public function setGenerator(GeneratorInterface $generator)
    {
        $this->generator = $generator;
    }


    /**
     * Adds an argument where the value maps to a generator configuration.
     *
     * @param string $name The argument name
     * @param integer $mode The argument mode: InputArgument::REQUIRED or InputArgument::OPTIONAL
     * @param string $description A description text
     * @param mixed $default The default value (for InputArgument::OPTIONAL mode only)
     * @param string|callable $configMapping The name of the configuration value to map the argument value to.
     * @return GenerateCommand The current instance
     */
    protected function addConfigArgument(
        $name,
        $mode = null,
        $description = '',
        $default = null,
        $configMapping = null
    ) {
        $this->setConfigMapping($name, $configMapping);
        return $this->addArgument($name, $mode, $description, $default);
    }

    /**
     * Adds an option where the value maps to a generator configuration.
     *
     * @param string $name The option name
     * @param string $shortcut The shortcut (can be null)
     * @param integer $mode The option mode: One of the InputOption::VALUE_* constants
     * @param string $description A description text
     * @param mixed $default The default value (must be null for InputOption::VALUE_REQUIRED or InputOption::VALUE_NONE)
     * @param string|callable $configMapping The name of the configuration value to map the argument value to or an
     *  anonymous function which performs the mapping.
     * @return GenerateCommand The current instance
     */
    protected function addConfigOption(
        $name,
        $shortcut = null,
        $mode = null,
        $description = '',
        $default = null,
        $configMapping = null
    ) {
        $this->setConfigMapping($name, $configMapping);
        return $this->addOption($name, $shortcut, $mode, $description, $default);
    }

    /**
     * @param string $name
     * @param string $shortcut
     * @param integer $mode
     * @param string $description
     * @param mixed $default
     * @param string $cache
     * @return GenerateCommand
     */
    protected function addCacheOption(
        $name,
        $shortcut = null,
        $mode = null,
        $description = '',
        $default = null,
        $cache = null
    ) {
        $cacheMapping = function (Input $input, Config &$config) use ($name, $cache) {
            if ($input->getOption($name)) {
                $config->setWsdlCache($cache);
            }
        };
        return $this->addConfigOption($name, $shortcut, $mode, $description, $default, $cacheMapping);
    }

    /**
     * @param string $name
     * @param string $shortcut
     * @param integer $mode
     * @param string $description
     * @param mixed $default
     * @param string $feature
     * @return GenerateCommand
     */
    protected function addFeatureOption(
        $name,
        $shortcut = null,
        $mode = null,
        $description = '',
        $default = null,
        $feature = null
    ) {
        $featureMapping = function (Input $input, Config &$config) use ($name, $feature) {
            if ($input->getOption($name)) {
                $options = $config->getOptionFeatures();
                $options[] = $feature;
                $config->setOptionFeatures(array_unique($options));
            }
        };
        return $this->addConfigOption($name, $shortcut, $mode, $description, $default, $featureMapping);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Input and output options are in fact required so bail if they are not set.
        if (!$input->getOption('input') || !$input->getOption('output')) {
            throw new \RuntimeException('Not enough arguments. Please specify input and output options.');
        }

        // Initialize configuration with null values. They will be updated during mapping.
        $config = new Config(null, null);

        // Map arguments to configuration
        foreach ($this->inputConfigMapping as $mapping) {
            $mapping($input, $config);
        }

        // Some arguments interact. Prompt the user to determine how to react.
        if ($config->getOneFile() && $config->getClassNames()) {
            // Print different messages based on if more than one class is requested for generation
            if (sizeof($config->getClassNamesArray()) > 1) {
                $message = sprintf('You have selected to only generate some of the classes in the wsdl (%s) and to save them in one file. Continue?', $config->getClassNamesArray());
            } else {
                $message = 'You have selected to only generate one class and save it to a single file. If you have selected the service class and outputs this file to a directory where you previosly have generated the classes the file will be overwritten. Continue?';
            }
            $continue = $this->getHelper('dialog')->askConfirmation($output, '<question>' . $message . '</question>');
            if (!$continue) {
                return;
            }
        }

        // Only set the logger if the generator instance supports this.
        // setLogger() has not been added to GeneratorInterface for backwards compatibility reasons.
        // FIXME: v3
        if (method_exists($this->generator, 'setLogger')) {
            $this->generator->setLogger(new OutputLogger($output));
        }

        // Go generate!
        $this->generator->generate($config);
    }

    /**
     * @param $name
     * @param $configMapping
     */
    protected function setConfigMapping($name, $configMapping)
    {
        if (!empty($configMapping)) {
            if (!is_callable($configMapping)) {
                $configMapping = function (InputInterface $input, Config &$config) use ($name, $configMapping) {
                    $value = false;
                    if ($input->hasArgument($name)) {
                        $value = $input->getArgument($name);
                    } elseif ($input->hasOption($name)) {
                        $value = $input->getOption($name);
                    }
                    if (!empty($value)) {
                        $configClass = new \ReflectionClass($config);
                        $setter = $configClass->getMethod('set' . ucfirst($configMapping));
                        $setter->invoke($config, $value);
                    }
                };
            }
            $this->inputConfigMapping[] = $configMapping;
        }
    }
}
