<?php


namespace Wsdl2PhpGenerator\Console;


use Psr\Log\AbstractLogger;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A simple PSR compatible logger which logs to the console.
 *
 * @package Wsdl2PhpGenerator\Console
 */
class OutputLogger extends AbstractLogger
{

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * @param OutputInterface $output The console output to log to
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        // Colorize messages according to level.
        $levelWrappers = array(
            'critical' => 'error',
            'error' => 'error',
            'warning' => 'comment',
            'info' => 'info',
            'notice' => '',
            'debug' => '',
        );
        if (!empty($levelWrappers[$level])) {
            $message = '<'. $levelWrappers[$level] . '>' . $message . '</' . $levelWrappers[$level] . '>';
        }

        // Map log levels to verbosity settings.
        $levels = array('critical', 'error', 'warning', 'info', 'notice', 'debug');
        $verbosityLevels[Output::VERBOSITY_QUIET] = -1;
        $verbosityLevels[Output::VERBOSITY_NORMAL] = 3;
        $verbosityLevels[Output::VERBOSITY_VERBOSE] = 4;
        $verbosityLevels[Output::VERBOSITY_VERY_VERBOSE] = 5;
        $verbosityLevels[Output::VERBOSITY_DEBUG] = 5;

        // If level is greater than verbosity level then print the message.
        if (array_search($level, $levels) <= $verbosityLevels[$this->output->getVerbosity()]) {
            $this->output->writeln($message);
        }
    }
}
