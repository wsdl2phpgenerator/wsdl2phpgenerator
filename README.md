# wsdl2phpgenerator
[![Build Status](https://travis-ci.org/wsdl2phpgenerator/wsdl2phpgenerator.png?branch=master)](https://travis-ci.org/wsdl2phpgenerator/wsdl2phpgenerator)
[![Coverage Status](https://coveralls.io/repos/wsdl2phpgenerator/wsdl2phpgenerator/badge.png)](https://coveralls.io/r/wsdl2phpgenerator/wsdl2phpgenerator)

Simple WSDL to PHP classes converter. Takes a WSDL file and outputs class files ready to use.

Uses the [MIT licence](http://www.opensource.org/licenses/mit-license.php).

## Contributors
Originally developed by [@walle](https://github.com/walle) and includes bugfixes and improvements from [@vakopian](https://github.com/vakopian), [@statikbe](https://github.com/statikbe/), [@ecolinet](https://github.com/ecolinet), [@nuth](https://github.com/nuth/) and [@kasperg](https://github.com/kasperg/).

Pull requests are very welcome.

## Mailing list

There is a mailing list for the project at [https://groups.google.com/forum/#!forum/wsdl2phpgenerator](https://groups.google.com/forum/#!forum/wsdl2phpgenerator)

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

$generator = Wsdl2PhpGenerator\Generator::instance();
$generator->generate(
	new Wsdl2PhpGenerator\Config( SOAPSERVICE, SOAPDIR ) 
);
?>
```
