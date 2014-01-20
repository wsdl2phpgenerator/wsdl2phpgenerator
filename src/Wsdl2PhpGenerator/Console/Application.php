<?php


namespace Wsdl2PhpGenerator\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * The base Wsdl2PhpGenerator console application.
 *
 * @package Wsdl2PhpGenerator\Console
 */
class Application extends SymfonyApplication
{

    protected function getDefaultInputDefinition()
    {
        // To preserve backwards compatibility we strip shortcuts from default options as they overlap with the
        // shortcuts from previous versions of Wsdl2PhpGenerator.
        // TODO: v3: Remove this for version 3.x where we can break backwards compatibility.
        $removeShortcuts = array('n', 'q');

        $updatedOptions = array();
        $inputDefinition = parent::getDefaultInputDefinition();
        foreach ($inputDefinition->getOptions() as $option) {
            if (in_array($option->getShortcut(), $removeShortcuts)) {
                // The shortcut should be stripped so create a replacement option without it.
                $updatedOptions[] = new InputOption(
                    $option->getName(),
                    null,
                    InputOption::VALUE_NONE,
                    $option->getDescription(),
                    null
                );
            } else {
                // No shortcut collision so we can reuse the existing option.
                $updatedOptions[] = $option;
            }
        }
        // Replace the original options with the updated set.
        $inputDefinition->setOptions($updatedOptions);

        return $inputDefinition;
    }

    // Make the application a single command tool to maintain backwards compatibility.
    // See http://symfony.com/doc/current/components/console/single_command_tool.html.
    // TODO: Remove this for version 3.x where we might have multiple commands.

    protected function getCommandName(InputInterface $input)
    {
        $command = new GenerateCommand();
        return $command->getName();
    }

    protected function getDefaultCommands()
    {
        // Keep the core default commands to have the HelpCommand
        // which is used when using the --help option
        $defaultCommands = parent::getDefaultCommands();
        $defaultCommands[] = new GenerateCommand();

        return $defaultCommands;
    }

    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        // clear out the normal first argument, which is the command name
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
} 
