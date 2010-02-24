<?php
/**
 * @package phpSource
 */

namespace phpSource;

/**
 * Include the needed files
 */
require_once \dirname(__FILE__).'/PhpDocElement.php';

/**
 * Class that contains static methods to create preset doc elements
 *
 * @package phpSource
 * @author Fredrik Wallgren <fredrik@wallgren.me>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class PhpDocElementFactory
{
  /**
   * Creates a param element
   *
   * @param string $dataType The name of the datatype of the variable
   * @param string $name The name of the variable
   * @param string $description
   *
   * @throws \Exception Throws exception if no name is supplied
   *
   * @return \phpSource\PhpDocElement The created element
   */
  public static function getParam($dataType, $name, $description)
  {
    if (\strlen($name) == 0)
    {
      throw new \Exception('A parameter must have a name!');
    }

    if ($name[0] == '$')
    {
      $name = \substr($name, 1);
    }
    
    return new \phpSource\PhpDocElement('param', $dataType, $name, $description);
  }

  /**
   * Creates a throws element
   *
   * @param string $exception
   * @param string $description
   * @return \phpSource\PhpDocElement The created element
   */
  public static function getThrows($exception, $description)
  {
    return new \phpSource\PhpDocElement('throws', $exception, '', $description);
  }

  /**
   * Creates a throws element
   *
   * @param string $dataType The name of the datatype
   * @param string $name The name of the variable
   * @param string $description Description of the variable
   * @return \phpSource\PhpDocElement The created element
   */
  public static function getVar($dataType, $name, $description)
  {
    return new \phpSource\PhpDocElement('var', $dataType, $name, $description);
  }

  /**
   * Creates a access element
   *
   * @param string $dataType The name of the datatype
   * @return \phpSource\PhpDocElement The created element
   */
  public static function getAccess($dataType)
  {
    return new \phpSource\PhpDocElement('access', $dataType, '', '');
  }

  /**
   * Creates a access element with the access public
   *
   * @return \phpSource\PhpDocElement The created element
   */
  public static function getPublicAccess()
  {
    return new \phpSource\PhpDocElement('access', 'public', '', '');
  }

  /**
   * Creates a access element with the access private
   *
   * @return \phpSource\PhpDocElement The created element
   */
  public static function getPrivateAccess()
  {
    return new \phpSource\PhpDocElement('access', 'private', '', '');
  }

  /**
   * Creates a access element with the access protected
   *
   * @return \phpSource\PhpDocElement The created element
   */
  public static function getProtectedAccess()
  {
    return new \phpSource\PhpDocElement('access', 'protected', '', '');
  }

  /**
   * Creates a package element
   *
   * @param string $package The name of the package
   * @return \phpSource\PhpDocElement The created element
   */
  public static function getPackage($package)
  {
    return new \phpSource\PhpDocElement('package', $package, '', '');
  }

  /**
   * Creates a author element
   *
   * @param string $author The name of the author
   * @return \phpSource\PhpDocElement The created element
   */
  public static function getAuthor($author)
  {
    return new \phpSource\PhpDocElement('author', $author, '', '');
  }

  /**
   * Creates a return element
   *
   * @param string $dataType The name of the datatype
   * @param string $description The description of the return value
   * @return \phpSource\PhpDocElement The created element
   */
  public static function getReturn($dataType, $description)
  {
    return new \phpSource\PhpDocElement('return', $dataType, '', $description);
  }

  /**
   * Creates a abstract element
   *
   * @return \phpSource\PhpDocElement The created element
   */
  public static function getAbstract()
  {
    return new \phpSource\PhpDocElement('abstract', '', '', '');
  }

  /**
   * Creates a final element
   *
   * @return \phpSource\PhpDocElement The created element
   */
  public static function getFinal()
  {
    return new \phpSource\PhpDocElement('final', '', '', '');
  }

  /**
   * Creates a depricated element
   *
   * @param string $information The description of why the element is depticated etc.
   * @return \phpSource\PhpDocElement The created element
   */
  public static function getDepricated($information = '')
  {
    return new \phpSource\PhpDocElement('depricated', '', '', $information);
  }

  /**
   * Creates a licence element
   *
   * @param string $information Information about the licence
   * @return \phpSource\PhpDocElement The created element
   */
  public static function getLicence($information)
  {
    return new \phpSource\PhpDocElement('licence', '', '', $information);
  }
}