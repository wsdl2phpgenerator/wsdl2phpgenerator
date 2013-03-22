<?php
/**
 * @package Wsdl2PhpGenerator
 */

/**
 * Gettext should not be required - Thanks jeichhor
 */
if (!function_exists("_")) {
    public
    function _($str)
    {
        return gettext($str);
    }
}

if (!function_exists("gettext")) {
    public
    function gettext($str)
    {
        return $str;
    }
}

/**
 * Include the needed files
 */
require_once dirname(__FILE__) . '/lib/cli/Cli.php';
require_once dirname(__FILE__) . '/lib/config/FileConfig.php';

require_once dirname(__FILE__) . '/src/Generator.php';

// Try to read the config file if any
try {
    $config = new FileConfig(dirname(__FILE__) . '/conf/settings.conf');
    $locale = $config->get('language');

    $domain = 'messages';
    $lcDir = 'LC_MESSAGES';
    $path = 'conf/translations';
    $loc = substr($locale, 0, 5);
    $file = $path . '/' . $loc . '/' . $lcDir . '/' . $domain . '.mo';

    if (file_exists($file) == false) {
        throw new Exception('The selected language file (' . $file . ') does not exist!');
    }

    bindtextdomain($domain, $path);
    textdomain($domain);
    setlocale(LC_ALL, $locale);
} catch (Exception $e) {
    // This should be the no file exception, then use the default settings
}

// Start
$cli = new Cli('wsdl2php', '[OPTIONS] -i wsdlfile -o directory', '1.5.2');
$cli->addFlag('-e', _('If all classes should be guarded with if(!class_exists) statements'), true, false);
$cli->addFlag('-t', _('If no type constructor should be generated'), true, false);
$cli->addFlag('-s', _('If the output should be a single file'), true, false);
$cli->addFlag('-v', _('If the output to the console should be verbose'), true, false);
$cli->addFlag('-i', _('The input wsdl file'), false, true);
$cli->addFlag('-o', _('The output directory or file if -s is used (in that case, .php will be appened to file name)'), false, true);
$cli->addFlag('-n', _('Use namespace with the name'), false, false);
$cli->addFlag('-c', _("A comma separated list of classnames to generate.\nIf this is used only classes that exist in the list will be generated.\nIf the service is not in this list and the -s flag is used\nthe filename will be the name of the first class that is generated"), false, false);
$cli->addFlag('-p', _('The prefix to use for the generated classes'), false, false);
$cli->addFlag('-q', _('The suffix to use for the generated classes'), false, false);
$cli->addFlag('--sharedTypes', _('If multiple class got the name, the first will be used, other will be ignored'), true, false);
$cli->addFlag('--createAccessors', _('Create getter and setter methods for member variables'), true, false);
$cli->addFlag('--constructorNull', _('Create getter and setter methods for member variables'), true, false);
$cli->addFlag('--noIncludes', _('Do not add include_once statements for loading individual files'), true, false);
$cli->addFlag('--singleElementArrays', _('Adds the option to use single element arrays to the client'), true, false);
$cli->addFlag('--xsiArrayType', _('Adds the option to use xsi arrays to the client'), true, false);
$cli->addFlag('--waitOneWayCalls', _('Adds the option to use wait one way calls to the client'), true, false);
$cli->addFlag('--cacheNone', _('Adds the option to not cache the wsdl to the client'), true, false);
$cli->addFlag('--cacheDisk', _('Adds the option to cache the wsdl on disk to the client'), true, false);
$cli->addFlag('--cacheMemory', _('Adds the option to cache the wsdl in memory to the client'), true, false);
$cli->addFlag('--cacheBoth', _('Adds the option to cache the wsdl in memory and on disk to the client'), true, false);
$cli->addFlag('--gzip', _('Adds the option to compress the wsdl with gzip to the client'), true, false);
$cli->addFlag('-h', _('Show this help'), true, false);

$cli->addAlias('-e', '--classExists');
$cli->addAlias('-e', '--exists');
$cli->addAlias('-t', '--noTypeConstructor');
$cli->addAlias('-s', '--singleFile');
$cli->addAlias('-v', '--verbose');
$cli->addAlias('-i', '--input');
$cli->addAlias('-o', '--output');
$cli->addAlias('-n', '--namespace');
$cli->addAlias('-c', '--classes');
$cli->addAlias('-c', '--classNames');
$cli->addAlias('-c', '--classList');
$cli->addAlias('-p', '--prefix');
$cli->addAlias('-q', '--suffix');
$cli->addAlias('-h', '--help');
$cli->addAlias('-h', '--h');

$cli->validate($argv);

$singleFile = $cli->getValue('-s');
$classNames = trim($cli->getValue('-c'));

if ($singleFile && strlen($classNames) > 0) {
    // Print different messages based on if more than one class is requested for generation
    if (strpos($classNames, ',') !== false) {
        print printf(_('You have selected to only generate some of the classes in the wsdl(%s) and to save them in one file. Continue? [Y/n]'), $classNames) . PHP_EOL;
    } else {
        print _('You have selected to only generate one class and save it to a single file. If you have selected the service class and outputs this file to a directory where you previosly have generated the classes the file will be overwritten. Continue? [Y/n]') . PHP_EOL;
    }

    //TODO: Refactor this to cli class?

    // Force the user to supply a valid input
    while (true) {
        $cmd = readline(null); // Reads from the standard input

        if (in_array($cmd, array('', 'y', 'Y', 'yes'))) {
            break; // Continue
        } elseif (in_array($cmd, array('n', 'no', 'N'))) {
            exit; // Terminate
        }

        print _('Please select yes or no.') . PHP_EOL;
    }
}

$classExists = $cli->getValue('-e');
$verbose = $cli->getValue('-v');
$noTypeConstructor = $cli->getValue('-t');
$inputFile = $cli->getValue('-i');
$outputDir = $cli->getValue('-o');
$namespaceName = $cli->getValue('-n');
$prefix = $cli->getValue('-p');
$suffix = $cli->getValue('-q');
$sharedTypes = $cli->getValue('--sharedTypes');
$createAccessors = $cli->getValue('--createAccessors');
$constructorDefaultsToNull = $cli->getValue('--constructorNull');
$noIncludes = $cli->getValue('--noIncludes');

$optionsArray = array();
if ($cli->getValue('--singleElementArrays')) {
    $optionsArray[] = 'SOAP_SINGLE_ELEMENT_ARRAYS';
}
if ($cli->getValue('--xsiArrayType')) {
    $optionsArray[] = 'SOAP_USE_XSI_ARRAY_TYPE';
}
if ($cli->getValue('--waitOneWayCalls')) {
    $optionsArray[] = 'SOAP_WAIT_ONE_WAY_CALLS';
}
$wsdlCache = '';
if ($cli->getValue('--cacheNone')) {
    $wsdlCache = 'WSDL_CACHE_NONE';
} elseif ($cli->getValue('--cacheDisk')) {
    $wsdlCache = 'WSDL_CACHE_DISK';
} elseif ($cli->getValue('--cacheMemory')) {
    $wsdlCache = 'WSDL_CACHE_MEMORY';
} elseif ($cli->getValue('--cacheBoth')) {
    $wsdlCache = 'WSDL_CACHE_BOTH';
}
$gzip = '';
if ($cli->getValue('--gzip')) {
    $gzip = 'SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP';
}

$config = new Config($inputFile, $outputDir, $verbose, $singleFile, $classExists, $noTypeConstructor, $namespaceName, $optionsArray, $wsdlCache, $gzip, $classNames, $prefix, $suffix, $sharedTypes, $createAccessors, $constructorDefaultsToNull, $noIncludes);

$generator = Generator::instance();
$generator->generate($config);
