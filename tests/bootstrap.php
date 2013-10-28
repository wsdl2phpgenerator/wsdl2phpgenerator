<?php

use Symfony\Component\ClassLoader\UniversalClassLoader;

require_once __DIR__ . '/../vendor/autoload.php';

$classloader = new UniversalClassLoader();
$classloader->registerNamespace('Wsdl2PhpGenerator', array('tests'));
$classloader->register();
