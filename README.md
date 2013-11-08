# wsdl2phpgenerator
[![Latest Stable Version](https://poser.pugx.org/wsdl2phpgenerator/wsdl2phpgenerator/v/stable.png)](https://packagist.org/packages/wsdl2phpgenerator/wsdl2phpgenerator)
[![Build Status](https://travis-ci.org/wsdl2phpgenerator/wsdl2phpgenerator.png?branch=master)](https://travis-ci.org/wsdl2phpgenerator/wsdl2phpgenerator)
[![Coverage Status](https://coveralls.io/repos/wsdl2phpgenerator/wsdl2phpgenerator/badge.png)](https://coveralls.io/r/wsdl2phpgenerator/wsdl2phpgenerator)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/wsdl2phpgenerator/wsdl2phpgenerator/badges/quality-score.png?s=23e602a86f75a79a2f1013caac99558f2464ce74)](https://scrutinizer-ci.com/g/wsdl2phpgenerator/wsdl2phpgenerator/)
[![Dependency Status](https://www.versioneye.com/user/projects/52697615632bac67b2002e93/badge.png)](https://www.versioneye.com/user/projects/52697615632bac67b2002e93)

Simple WSDL to PHP classes converter. Takes a WSDL file and outputs class files ready to use.

Uses the [MIT licence](http://www.opensource.org/licenses/mit-license.php).

## Contributors
Originally developed by [@walle](https://github.com/walle) and includes bugfixes and improvements from [@vakopian](https://github.com/vakopian), [@statikbe](https://github.com/statikbe/), [@ecolinet](https://github.com/ecolinet), [@nuth](https://github.com/nuth/), [@chriskl](https://github.com/chriskl/), [@dypa](https://github.com/dypa/) and [@kasperg](https://github.com/kasperg/).

Pull requests are very welcome.

## Mailing list

There is a mailing list for the project at [https://groups.google.com/forum/#!forum/wsdl2phpgenerator](https://groups.google.com/forum/#!forum/wsdl2phpgenerator)

## Usage

### Install from repo

1. Clone this repo `git clone git@github.com:wsdl2phpgenerator/wsdl2phpgenerator.git`

1. Install composer:
    * [Installation - *nix](http://getcomposer.org/doc/00-intro.md#installation-nix)
    * [Installation - Windows](http://getcomposer.org/doc/00-intro.md#installation-windows)

1. Run `composer install`

### Install from packagist

Add `"wsdl2phpgenerator/wsdl2phpgenerator": "dev-master"` and run `composer update`

### Executable
`./wsdl2php -i input.wsdl -o /tmp/my/directory/wsdl`

The directory is created if possible.

usage listed under `./wsdl2php -h`

### Code

```php
<?php
// Map `src`and `lib` folders to the Wsdl2PhpGenerator namespace in your
// favorite PSR-0 compatible classloader or require the files manually.

$generator = Wsdl2PhpGenerator\Generator::instance();
$generator->generate(
	new Wsdl2PhpGenerator\Config( SOAPSERVICE, SOAPDIR ) 
);
?>
```

## Versioning

This project aims to use [semantic versioning](http://semver.org/). The following consitutes the public API: 

  * `\Wsdl2PhpGenerator\GeneratorInterface`
  * `\Wsdl2PhpGenerator\ConfigInterface`

Changes to these means that the major version will be increased. Additional features and bug fixes increate minor and patch versions.
