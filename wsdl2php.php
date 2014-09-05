<?php
/**
 * @package Wsdl2PhpGenerator
 */
use Wsdl2PhpGenerator\Console\Application;
use Wsdl2PhpGenerator\Console\GenerateCommand;
use Wsdl2PhpGenerator\Generator;

if (file_exists('vendor/autoload.php'))
{
	require 'vendor/autoload.php';
}
else
{
	require __DIR__ . '/../../autoload.php';
}

$app = new Application('wsdl2php', '2.4.2');
$command = new GenerateCommand();
$command->setGenerator(Generator::getInstance());
$app->add($command);
$app->run();
