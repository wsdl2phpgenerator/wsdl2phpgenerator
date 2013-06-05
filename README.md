# wsdl2phpgenerator
[![Build Status](https://travis-ci.org/reload/wsdl2phpgenerator.png?branch=master)](https://travis-ci.org/reload/wsdl2phpgenerator)
[![Coverage Status](https://coveralls.io/repos/reload/wsdl2phpgenerator/badge.png?branch=code-coverage-coveralls)](https://coveralls.io/r/reload/wsdl2phpgenerator?branch=code-coverage-coveralls)

Simple WSDL to PHP classes converter. Takes a WSDL file and outputs class files ready to use.

Uses the [MIT licence](http://www.opensource.org/licenses/mit-license.php)

## Contributors
Originally developed by Fredrik Wallgren, https://github.com/walle/wsdl2phpgenerator

Includes bugfixes and improvements from:

* Vardan Akopian, https://github.com/vakopian/wsdl2phpgenerator
* http://www.statik.be, https://github.com/statikbe/wsdl2phpgenerator
* Eric Colinet, https://github.com/ecolinet/wsdl2phpgenerator
* Nuth, https://github.com/nuth/wsdl2phpgenerator
* Kasper Garnæs, https://github.com/kasperg/wsdl2phpgenerator

This fork has been created in an attempt to merge changes in forks of wsdl2phpgenerator which has never made it back into the original repository.

Pull requests are very welcome.

## Usage
Use the executable or the Generator class directly.

### Executable
`./wsdl2php -i input.wsdl -o /tmp/my/directory/wsdl`

The directory is created if possible.

usage listed under `./wsdl2php -h`

### Code

```php
<?php
require_once __DIR__."/path/here/Generator.php";

$generator = Generator::instance();
$generator->setDisplayCallback( function( $msg ) {
	echo "{$msg}\n";
});
$generator->generate( 
	new Config( SOAPSERVICE, SOAPDIR ) 
);
?>
```
