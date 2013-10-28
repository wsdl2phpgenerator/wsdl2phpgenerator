<?php
/**
 * @package Wsdl2PhpGenerator
 */
use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\Console\Application;
use Wsdl2PhpGenerator\Console\GenerateCommand;
use Wsdl2PhpGenerator\Generator;

require __DIR__ . '/vendor/autoload.php';

$app = new Application('wsdl2php', '2.1.0');
$command = new GenerateCommand();
$command->setGenerator(Generator::getInstance());
$app->add($command);
$app->run();
