<?php

use \Composer\Autoload\ClassLoader;

require_once __DIR__ . '/../vendor/autoload.php';

$classloader = new ClassLoader();
$classloader->set('Wsdl2PhpGenerator', array('tests'));
$classloader->register();
