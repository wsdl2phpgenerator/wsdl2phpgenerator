# wsdl2phpgenerator
[![Latest Stable Version](https://poser.pugx.org/wsdl2phpgenerator/wsdl2phpgenerator/v/stable.png)](https://packagist.org/packages/wsdl2phpgenerator/wsdl2phpgenerator)
[![Build Status](https://travis-ci.org/wsdl2phpgenerator/wsdl2phpgenerator.svg?branch=master)](https://travis-ci.org/wsdl2phpgenerator/wsdl2phpgenerator)
[![Code Coverage](https://scrutinizer-ci.com/g/wsdl2phpgenerator/wsdl2phpgenerator/badges/coverage.png?s=91798255fd973950b1e2d7478f99d6f6f80cf6da)](https://scrutinizer-ci.com/g/wsdl2phpgenerator/wsdl2phpgenerator/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/wsdl2phpgenerator/wsdl2phpgenerator/badges/quality-score.png?s=23e602a86f75a79a2f1013caac99558f2464ce74)](https://scrutinizer-ci.com/g/wsdl2phpgenerator/wsdl2phpgenerator/)
[![Dependency Status](https://www.versioneye.com/user/projects/52697615632bac67b2002e93/badge.png)](https://www.versioneye.com/user/projects/52697615632bac67b2002e93)

Simple WSDL to PHP classes converter. Takes a WSDL file and outputs class files ready to use.

Uses the [MIT licence](http://www.opensource.org/licenses/mit-license.php).

## Contributors
Originally developed by [@walle](https://github.com/walle) and includes bugfixes and improvements from [@vakopian](https://github.com/vakopian), [@statikbe](https://github.com/statikbe/),
[@ecolinet](https://github.com/ecolinet), [@nuth](https://github.com/nuth/), [@chriskl](https://github.com/chriskl/), [@RSully](https://github.com/RSully/), [@jrbasso](https://github.com/jrbasso/),
[@dypa](https://github.com/dypa/), [@Lafriks](https://github.com/Lafriks/), [@SamMousa](https://github.com/SamMousa/), [@garex](https://github.com/garex/), [@honzap](https://github.com/honzap/) and [@kasperg](https://github.com/kasperg/).

Pull requests are very welcome. Please read [our guidelines for contributing](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator/blob/master/CONTRIBUTING.md).

## Mailing list

There is a mailing list for the project at [https://groups.google.com/forum/#!forum/wsdl2phpgenerator](https://groups.google.com/forum/#!forum/wsdl2phpgenerator)

## Usage

### Standalone executable

1. Download [wsdl2phpgenerator-2.5.4.phar](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator/releases/download/2.5.4/wsdl2phpgenerator-2.5.4.phar) from [the latest release](https://github.com/wsdl2phpgenerator/wsdl2phpgenerator/releases/latest)
1. Run `php wsdl2phpgenerator-2.5.4.phar -i input.wsdl -o tmp/my/directory/wsdl`

The directory is created if possible.

Usage is listed under `./wsdl2php -h`


### Executable in Composer project

1. Add `"wsdl2phpgenerator/wsdl2phpgenerator": "2.5.4"`  to the `require` or `require-dev` section of your `composer.json` file
1. Run `composer update`
1. Run `./vendor/bin/wsdl2php -i input.wsdl -o tmp/my/directory/wsdl`

The directory is created if possible.

Usage is listed under `./vendor/bin/wsdl2php -h`

### Code

```php
<?php
// Map 'src' and 'lib' folders to the Wsdl2PhpGenerator namespace in your
// favorite PSR-0 compatible classloader or require the files manually.

$generator = new \Wsdl2PhpGenerator\Generator();
$generator->generate(
	new \Wsdl2PhpGenerator\Config( SOAPSERVICE, SOAPDIR )
);
?>
```

## Versioning

This project aims to use [semantic versioning](http://semver.org/). The following consitutes the public API: 

  * `\Wsdl2PhpGenerator\GeneratorInterface`
  * `\Wsdl2PhpGenerator\ConfigInterface`

Changes to these means that the major version will be increased. Additional features and bug fixes increate minor and patch versions.
