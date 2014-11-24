# What is new in wsdl2phpgenerator 3.0

Version 3.0 is the first major version release since development of wsdl2phpgenerator was resumed february 2013.

The new release introduces changes which makes it more flexible to use, easier to include in other projects, promotes contributions and reduces maintenance.

Development of 3.0 started in june 2014 and includes all features and bug fixes introduced in 2.x since.

This document is a walkthrough of the changes that break backwards compatibility. The intended audience is users of wsdl2phpgenerator 2.x who wish to upgrade to the new version and may have to make changes to their code accordingly.

## Separation of library and console application

This repository and Composer package, `wsdl2phpgenerator/wsdl2phpgenerator` will as of 3.0 only contain the wsdl2phpgenerator library. The intended users are developers who prefer to configure and invoke the code generation process programmatically. This reduces the number of dependencies for the package.

There is a separate repository and Composer package for the console application [`wsdl2phpgenerator/wsdl2phpgenerator-cli`](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator-cli). This repository will also contain future releases of the console application as phar files.

For discussion and pull request see issues [#117](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator/issues/117) and [#119](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator/issues/119).

## Configuration

### Set configuration values

The configuration interface and class have been refactored. There were previously dedicated accessors for each configuration setting. This has been replaced with general accessors.

```php
// 2.x
$config->setNamespaceName('Service');
// 3.x
$config->set('namespaceName', 'Service');
```

All configuration names are in `mixedCase` form.

This change allows us to add new configuration settings without changing `ConfigInterface` which would otherwise break backwards compatibility.

On the inside configuration is now handled by `symfony/options-resolver` which handles the job of managing required values, setting defaults etc.

For discussion and pull request see issues [#94](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator/issues/94) and [#101](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator/issues/101).


### Obsolete configuration options removed

wsdl2phpgenerator 2.x contained numerous configuration options. The project was started over 3 years ago and some of the options reflected old PHP development problems. PHP development has evolved and the project now requires PHP 5.3.

To avoid promoting poor practices and make maintenance easier the following configuration options have been removed.

* `classExists`: This is no longer relevant.
* `createAccessors`: This is now enabled by default.
* `noIncludes`: There are now no includes in the generated classes. Instead use `autoload.php` or your own autloading.
* `prefix`: Use a namespace instead.
* `suffix`: Use a namespace instead.
* `singleFile`: Autoloading ensures that only used classes are loading. Use an opcode cache to improve performance.

For discussion and pull request see issues [#106](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator/issues/106) and [#127](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator/issues/127).

### SOAP client configuration option as array

Some configuration options provided in wsdl2phpgenerator 2.x were related to configuration of the PHP SoapClient. Not all SoapClient options were supported and this was the cause of many pull requests.

In 3.0 there is now one generic configuration option `soapClientOptions` which accepts an array and passes it along to [the `SoapClient` constructor](http://php.net/manual/en/soapclient.soapclient.php). Thus wsdl2phpgenerator now supports all configuration options supported by the SOAP client class.

Consequently the following configuration options have been removed.

* `optionsFeatures`
* `wsdlCache`
* `compression`

For discussion and pull request see issues [#105](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator/issues/105) and [#154](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator/issues/154).

## Generated code

### Properties can no longer be public

In 3.0 classes can no longer be generated with public properties. Instead properties are always protected and accessors are always generated.

For discussion and pull request see issues [#106](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator/issues/106) and [#127](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator/issues/127).

### Type names now respect namespaces

When generating classes namespaces are now taken into account when checking for name clashes. This means that there should be no classes with unnecessary suffixes.

```php
// 2.x
$generator = new \Service\GeneratorCustom();
// 3.0
$generator = new \Service\Generator();
```

For discussion and pull request see issue [#139](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator/issues/139).

### Classloading through `autoload.php` 

In 2.x the Service class would also contain `include` statements for all other generated classes.

In 3.0 wsdl2phpgenerator now generates an `autoload.php` file with the generated classes. Generated classes can be autoloaded by including this file or by adding them to an existing autoloader.

For discussion and pull request see issues [#106](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator/issues/106) and [#127](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator/issues/127).

### DateTime objects for DateTime properties

Accessors for properties which represent a DateTime now require a DateTime arguments and return DateTime values.

This promotes use of proper data structures instead of juggling strings.

For discussion and pull request see issues [#81](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator/pull/81) and [#125](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator/issues/125).

### Type hints for array arguments

Setters for array properties now require an array argument.

This should make it easier to ensure that valid objects are created.

### Method visibility no longer declared in DocBLock

`@access public` has been removed from DocBlocks. It is obsolete as visibility is declared directly as a part of each method.

For discussion and pull request see issues [#134](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator/pull/134) and [#144](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator/issues/14).

## Extending wsdl2phpgenerator

### Interface changes

The `\Wsdl2PhpGenerator\GeneratorInterface` and `\Wsdl2PhpGenerator\ConfigInterface` interfaces have been updated.

`GeneratorInterface` now has support for injecting a logger. `ConfigInterface` now has generic accessors instead of specific methods for each configuration option.