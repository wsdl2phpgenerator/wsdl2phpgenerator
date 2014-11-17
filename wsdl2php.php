<?php
/**
 * @package Wsdl2PhpGenerator
 */
use Wsdl2PhpGenerator\Console\Application;
use Wsdl2PhpGenerator\Console\GenerateCommand;
use Wsdl2PhpGenerator\Generator;

// Require Composer autoloader depending on usage:
// From own project...
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
// ... or when included in another project.
} else {
    require __DIR__ . '/../../autoload.php';
}

$app = new Application('wsdl2php', '2.5.5');
$command = new GenerateCommand();
$command->setGenerator(Generator::getInstance());
$app->add($command);
$app->run();
