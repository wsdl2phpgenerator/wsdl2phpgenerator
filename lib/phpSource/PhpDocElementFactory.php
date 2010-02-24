<?php
/**
 * @package phpSource
 */

/**
 * Include the needed files
 */
require_once dirname(__FILE__).'/PhpDocElement.php';

/**
 * Class that contains static methods to create preset doc elements
 *
 * @package phpSource
 * @author Fredrik Wallgren <fredrik@wallgren.me>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class phpSourcePhpDocElementFactory
{
  /**
   * Creates a param element
   *
   * @param string $dataType The name of the datatype of the variable
   * @param string $name The name of the variable
   * @param string $description
   *
   * @throws Exception Throws exception if no name is supplied
   *
   * @return phpSourcePhpDocElement The created element
   */
  public static function getParam($dataType, $name, $description)
  {
    if (strlen($name) == 0)
    {
      throw new Exception('A parameter must have a name!');
    }

    if ($name[0] == '$')
    {
      $name = substr($name, 1);
    }
    
    return new phpSourcePhpDocElement('param', $dataType, $name, $description);
  }

  /**
   * Creates a throws element
   *
   * @param string $exception
   * @param string $description
   * @return phpSourcePhpDocElement The created element
   */
  public static function getThrows($exception, $description)
  {
    return new phpSourcePhpDocElement('throws', $exception, '', $description);
  }

  /**
   * Creates a throws element
   *
   * @param string $dataType The name of the datatype
   * @param string $name The name of the variable
   * @param string $description Description of the variable
   * @return phpSourcePhpDocElement The created element
   */
  public static function getVar($dataType, $name, $description)
  {
    return new phpSourcePhpDocElement('var', $dataType, $name, $description);
  }

  /**
   * Creates a access element
   *
   * @param string $dataType The name of the datatype
   * @return phpSourcePhpDocElement The created element
   */
  public static function getAccess($dataType)
  {
    return new phpSourcePhpDocElement('access', $dataType, '', '');
  }

  /**
   * Creates a access element with the access public
   *
   * @return phpSourcePhpDocElement The created element
   */
  public static function getPublicAccess()
  {
    return new phpSourcePhpDocElement('access', 'public', '', '');
  }

  /**
   * Creates a access element with the access private
   *
   * @return phpSourcePhpDocElement The created element
   */
  public static function getPrivateAccess()
  {
    return new phpSourcePhpDocElement('access', 'private', '', '');
  }

  /**
   * Creates a access element with the access protected
   *
   * @return phpSourcePhpDocElement The created element
   */
  public static function getProtectedAccess()
  {
    return new phpSourcePhpDocElement('access', 'protected', '', '');
  }

  /**
   * Creates a package element
   *
   * @param string $package The name of the package
   * @return phpSourcePhpDocElement The created element
   */
  public static function getPackage($package)
  {
    return new phpSourcePhpDocElement('package', $package, '', '');
  }

  /**
   * Creates a author element
   *
   * @param string $author The name of the author
   * @return phpSourcePhpDocElement The created element
   */
  public static function getAuthor($author)
  {
    return new phpSourcePhpDocElement('author', $author, '', '');
  }

  /**
   * Creates a return element
   *
   * @param string $dataType The name of the datatype
   * @param string $description The description of the return value
   * @return phpSourcePhpDocElement The created element
   */
  public static function getReturn($dataType, $description)
  {
    return new phpSourcePhpDocElement('return', $dataType, '', $description);
  }

  /**
   * Creates a abstract element
   *
   * @return phpSourcePhpDocElement The created element
   */
  public static function getAbstract()
  {
    return new phpSourcePhpDocElement('abstract', '', '', '');
  }

  /**
   * Creates a final element
   *
   * @return phpSourcePhpDocElement The created element
   */
  public static function getFinal()
  {
    return new phpSourcePhpDocElement('final', '', '', '');
  }

  /**
   * Creates a depricated element
   *
   * @param string $information The description of why the element is depticated etc.
   * @return phpSourcePhpDocElement The created element
   */
  public static function getDepricated($information = '')
  {
    return new phpSourcePhpDocElement('depricated', '', '', $information);
  }

  /**
   * Creates a licence element
   *
   * @param string $information Information about the licence
   * @return phpSourcePhpDocElement The created element
   */
  public static function getLicence($information)
  {
    return new phpSourcePhpDocElement('licence', '', '', $information);
  }
}