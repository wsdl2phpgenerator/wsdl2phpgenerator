# Usage and options

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
