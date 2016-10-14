# wsdl2phpgenerator
[![Latest Stable Version](https://poser.pugx.org/wsdl2phpgenerator/wsdl2phpgenerator/v/stable.png)](https://packagist.org/packages/wsdl2phpgenerator/wsdl2phpgenerator)
[![Build Status](https://travis-ci.org/wsdl2phpgenerator/wsdl2phpgenerator.svg?branch=master)](https://travis-ci.org/wsdl2phpgenerator/wsdl2phpgenerator)
[![Code Coverage](https://scrutinizer-ci.com/g/wsdl2phpgenerator/wsdl2phpgenerator/badges/coverage.png?s=91798255fd973950b1e2d7478f99d6f6f80cf6da)](https://scrutinizer-ci.com/g/wsdl2phpgenerator/wsdl2phpgenerator/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/wsdl2phpgenerator/wsdl2phpgenerator/badges/quality-score.png?s=23e602a86f75a79a2f1013caac99558f2464ce74)](https://scrutinizer-ci.com/g/wsdl2phpgenerator/wsdl2phpgenerator/)
[![Dependency Status](https://www.versioneye.com/user/projects/52697615632bac67b2002e93/badge.png)](https://www.versioneye.com/user/projects/52697615632bac67b2002e93)

Simple WSDL to PHP classes converter. Takes a WSDL file and outputs class files ready to use.

Uses the [MIT license](http://www.opensource.org/licenses/mit-license.php).

> **Announcement**: We are looking to add one or two co-maintainers with commit access to help bring this project forward, review pull requests and respond to issues. If you have contributed to this project or are otherwise actively involved in open source and have a GitHub profile for review, ping [@kasperg](https://github.com/kasperg) to express your interest.

## New major version: 3.0

A new major version of wsdl2phpgenerator has recently been released: 3.0.

This introduces changes to both configuration and generated code. The changes makes it more flexible to use, easier to include in other projects, promotes contributions and reduces maintenance.

2.x users are encourage to read [a walkthrough of what is new in 3.0](docs/whats-new-in-3.0.md).

## Contributors
Originally developed by [@walle](https://github.com/walle) and includes bug fixes and improvements from [@vakopian](https://github.com/vakopian), [@statikbe](https://github.com/statikbe/), [@ecolinet](https://github.com/ecolinet), [@nuth](https://github.com/nuth/), [@chriskl](https://github.com/chriskl/), [@RSully](https://github.com/RSully/), [@jrbasso](https://github.com/jrbasso/), [@dypa](https://github.com/dypa/), [@Lafriks](https://github.com/Lafriks/), [@SamMousa](https://github.com/SamMousa/), [@xstefanox](https://github.com/xstefanox/), [@garex](https://github.com/garex/), [@honzap](https://github.com/honzap/), [@jk](https://github.com/jk/), [@sheeep](https://github.com/sheeep/), [@colinodell](https://github.com/colinodell/), [@red-led](https://github.com/red-led/), [@ivol84](https://github.com/ivol84/), [@wasinger](https://github.com/wasinger/), [@devlead](https://github.com/devlead/), [@NoUseFreak](https://github.com/nousefreak/), [@HighOnMikey](https://github.com/highonmikey/), [@theHarvester](https://github.com/theHarvester), [@fduch](https://github.com/fduch), [@methodin](https://github.com/methodin), [@nkm](https://github.com/nkm/), [@jongotlin](https://github.com/jongotlin/), [@yethee](https://github.com/yethee/), [@rindeal](https://github.com/rindeal/)
and [@kasperg](https://github.com/kasperg/).

Pull requests are very welcome. Please read [our guidelines for contributing](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator/blob/master/CONTRIBUTING.md).

## Mailing list

There is a mailing list for the project at [https://groups.google.com/forum/#!forum/wsdl2phpgenerator](https://groups.google.com/forum/#!forum/wsdl2phpgenerator)

## Installation

Add wsdl2phpgenerator to your [Composer](https://getcomposer.org/doc/00-intro.md) project:

```bash
composer require wsdl2phpgenerator/wsdl2phpgenerator
```

The project will also be available as [a command line application](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator-cli) which can be downloaded as a phar file.


## Usage

To generate classes create a `Generator` instance and pass it a `Config` instance.

```php
$generator = new \Wsdl2PhpGenerator\Generator();
$generator->generate(
	new \Wsdl2PhpGenerator\Config(array(
        'inputFile' => 'input.wsdl',
        'outputDir' => '/tmp/output'
    ))
);
```

After generating the code then configure your existing autoloader accordingly. The generated code also comes with a simple `autoload.php` file which can be included directly. This registers a simple autoloader for the generated classes.

#### Example usage

The following example will generate code from a web service, load the generated classes, call the web service and return the result over the course of a single process.

```php
$generator = new \Wsdl2PhpGenerator\Generator();
$generator->generate(
	new \Wsdl2PhpGenerator\Config(array(
        'inputFile' => 'http://www.webservicex.net/CurrencyConvertor.asmx?WSDL',
        'outputDir' => '/tmp/CurrencyConverter'
    ))
);

require '/tmp/CurrencyConverter/autoload.php';

// A class will generated representing the service.
// It is named after the element in the WSDL and has a method for each operation.
$service = new \CurrencyConvertor();
$request = new \ConversionRate(\Currency::USD, \Currency::EUR);
$response = $service->ConversionRate($request);

echo $response->getConversionRateResult();
```

Note that this is not recommended usage. Normally code generation and web services calls will be two separate processes.

### Options

The generator supports a range of options which can be set in the configuration.

#### `inputFile`

The path or url to the WSDL to generate classes from.

#### `outputDir`

The directory to place the generated classes in. It will be created if it does not already exist.

#### `namespaceName`

The [namespace](http://php.net/manual/en/language.namespaces.php) to use for the generated classes. If not set classes will be generated without a namespace.


##### Example usage

The following configuration will place generated code from the [CDYNE Weather web service](http://wiki.cdyne.com/?title=CDYNE_Weather) under the `CDyne\Weather` namespace:

```php
$generator = new \Wsdl2PhpGenerator\Generator();
$generator->generate(
    new \Wsdl2PhpGenerator\Config(array(
        'inputFile' => 'http://wsf.cdyne.com/WeatherWS/Weather.asmx?wsdl',
        'outputDir' => '/tmp/weather',
        'namespaceName' => 'CDyne\Weather'
    ))
);
```

#### `classNames`

A comma-separared list or array of class names to generate. All other classes in the WSDL will be ignored.

This option is deprecated and will be removed in 4.0.0. Use `operationNames` instead.

##### Example usage

The following configuration will only generate `AmazonEC2` and `CopyImageType` classes from the Amazon EC2 webservice.

```php
$generator = new \Wsdl2PhpGenerator\Generator();
$generator->generate(
    new \Wsdl2PhpGenerator\Config(array(
        'inputFile' => 'https://s3.amazonaws.com/ec2-downloads/2013-10-01.ec2.wsdl',
        'outputDir' => '/tmp/amazon',
        'classNames' => 'AmazonEC2, CopyImageType'
    ))
);
```
#### `operationNames`

A comma-separated list or array of service operations to generate. This will only generate types that are needed for selected operations. The generated service class will only contain selected operation.

##### Example usage

The following configuration will generate operations and types for `ReplaceRouteTableAssociation` and `RequestSpotInstances` operations.

```php
$generator = new \Wsdl2PhpGenerator\Generator();
$generator->generate(
    new \Wsdl2PhpGenerator\Config(array(
        'inputFile' => 'https://s3.amazonaws.com/ec2-downloads/2013-10-01.ec2.wsdl',
        'outputDir' => '/tmp/amazon',
        'operationNames' => 'ReplaceRouteTableAssociation, RequestSpotInstances'
    ))
);
```
#### `sharedTypes`

If enabled this makes all types with the same identify use the same class and only generate it once. The default solution is to prepend numbering to avoid name clashes.

#### `constructorParamsDefaultToNull`

If enabled this sets the default value of all parameters in all constructors to `null`. If this is used then properties must be set using accessors.

#### `proxy`

Specify a proxy to use when accessing the WSDL and other external ressources. This option should be used instead of [the proxy options support by the PHP `SoapClient`] (http://php.net/manual/en/soapclient.soapclient.php) as wsdl2phpgenerator uses more than the SOAP client to extract information.

The following formats are supported:

* An array with the following keys `host`, `port`, `login` and `password` matching [the proxy options support by the PHP `SoapClient`] (http://php.net/manual/en/soapclient.soapclient.php)
* A string in an URL-like format

The proxy information is used by is used when accessing the WSDL to generate the code and for subsequent requests to the SOAP service.

##### Example usage

The following configuration will use a proxy to access the [Google DoubleClick Ad Exchange Buyer SOAP API](https://developers.google.com/ad-exchange/buyer-soap/):

```php
$generator = new \Wsdl2PhpGenerator\Generator();
$generator->generate(
    new \Wsdl2PhpGenerator\Config(array(
        'inputFile' => 'https://ads.google.com/apis/ads/publisher/v201306/ActivityService?wsdl',
        'outputDir' => '/tmp/amazon',
        'proxy' => 'tcp://user:secret@192.168.0.1:8080'
    ))
);
```

#### `soapClientClass`

The base class to use for generated services. This should be a subclass of the [PHP `SoapClient`](http://php.net/manual/en/class.soapclient.php).

Examples of third party SOAP client implementations which can be used:

* [BeSimpleSoapClient](https://github.com/BeSimple/BeSimpleSoapClient)
* [ZendFramework2 SOAP Component](https://github.com/zendframework/Component_ZendSoap) 
* [soap-plus](https://github.com/dcarbone/soap-plus)
* [SoapClientEx](https://gist.github.com/RobThree/4117914)

Note that it is the responsibility of the surrounding code to ensure that the base class is available during code generation and when calling web services.

##### Example usage

The following configuration will use the BeSimple SOAP client as base class:

```php
$generator = new \Wsdl2PhpGenerator\Generator();
$generator->generate(
    new \Wsdl2PhpGenerator\Config(array(
        'inputFile' => 'input.wsdl',
        'outputDir' => '/tmp/output',
        'soapClientClass' => '\BeSimple\SoapClient\SoapClient'
    ))
);
```

#### `soapClientOptions`

An array of configuration options to pass to the SoapClient. They will be used when accessing the WSDL to generate the code and as defaults for subsequent requests to the SOAP service. The PHP documentation has [a list of supported options](http://php.net/manual/en/soapclient.soapclient.php#refsect1-soapclient.soapclient-parameters).

The list of options for the client can be extended by using more advanced `SoapClient` implementations.

Note that wsdl2phpgenerator expects the `features` option to contain `SOAP_SINGLE_ELEMENT_ARRAYS`. [This ensures that type hints are consistent even if sequences only contain one element](http://php.net/manual/en/soapclient.soapclient.php#73082). If the `features` option is set explicitly in `soapClientOptions` the `SOAP_SINGLE_ELEMENT_ARRAYS` must also be added explicitly.

##### Example usage

The following configuration will enable basic authentication and set the connection timeout to 60 seconds.

```php
$generator = new \Wsdl2PhpGenerator\Generator();
$generator->generate(
    new \Wsdl2PhpGenerator\Config(array(
        'inputFile' => 'input.wsdl',
        'outputDir' => '/tmp/output',
        'soapClientOptions' => array(
        	'authentication' => SOAP_AUTHENTICATION_BASIC,
        	'login' => 'username',
        	'password' => 'secret',
        	'connection_timeout' => 60
    ))
));
````

## Versioning

This project aims to use [semantic versioning](http://semver.org/). The following constitutes the public API:

  * `\Wsdl2PhpGenerator\GeneratorInterface`
  * `\Wsdl2PhpGenerator\ConfigInterface`
  * Generated code

Backwards incompatible changes to these means that the major version will be increased. Additional features and bug fixes increate minor and patch versions.
