<?php

require_once __DIR__ . '/../vendor/autoload.php';

$classloader = new \Composer\Autoload\ClassLoader();
$classloader->set('Wsdl2PhpGenerator', array('tests'));
$classloader->register();
