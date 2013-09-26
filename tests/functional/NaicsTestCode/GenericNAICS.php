<?php

include_once('GetNAICSByID.php');
include_once('GetNAICSByIDResponse.php');
include_once('NAICSList.php');
include_once('NAICS.php');
include_once('GetNAICSByIndustry.php');
include_once('GetNAICSByIndustryResponse.php');
include_once('GetNAICSGroupByID.php');
include_once('GetNAICSGroupByIDResponse.php');


/**
 * The North American Industry Classification System (NAICS) has replaced the U.S. Standard Industrial Classification (SIC) system. NAICS will reshape the way we view our changing economy.NAICS was developed jointly by the U.S., Canada, and Mexico to provide new comparability in statistics about business activity across North America.
 * 
 */
class GenericNAICS extends \SoapClient
{

  /**
   * 
   * @var array $classmap The defined classes
   * @access private
   */
  private static $classmap = array(
    'GetNAICSByID' => '\\GetNAICSByID',
    'GetNAICSByIDResponse' => '\\GetNAICSByIDResponse',
    'NAICSList' => '\\NAICSList',
    'NAICS' => '\\NAICS',
    'GetNAICSByIndustry' => '\\GetNAICSByIndustry',
    'GetNAICSByIndustryResponse' => '\\GetNAICSByIndustryResponse',
    'GetNAICSGroupByID' => '\\GetNAICSGroupByID',
    'GetNAICSGroupByIDResponse' => '\\GetNAICSGroupByIDResponse');

  /**
   * 
   * @param array $options A array of config values
   * @param string $wsdl The wsdl file to use
   * @access public
   */
  public function __construct(array $options = array(), $wsdl = 'http://www.webservicex.net/GenericNAICS.asmx?WSDL')
  {
    foreach (self::$classmap as $key => $value) {
      if (!isset($options['classmap'][$key])) {
        $options['classmap'][$key] = $value;
      }
    }
    
    parent::__construct($wsdl, $options);
  }

  /**
   * Get NAICS details by NAICS code
   * 
   * @param GetNAICSByID $parameters
   * @access public
   * @return GetNAICSByIDResponse
   */
  public function GetNAICSByID(GetNAICSByID $parameters)
  {
    return $this->__soapCall('GetNAICSByID', array($parameters));
  }

  /**
   * Get NAICS details by Industry Name
   * 
   * @param GetNAICSByIndustry $parameters
   * @access public
   * @return GetNAICSByIndustryResponse
   */
  public function GetNAICSByIndustry(GetNAICSByIndustry $parameters)
  {
    return $this->__soapCall('GetNAICSByIndustry', array($parameters));
  }

  /**
   * Get NAICS details by NAICS group code
   * 
   * @param GetNAICSGroupByID $parameters
   * @access public
   * @return GetNAICSGroupByIDResponse
   */
  public function GetNAICSGroupByID(GetNAICSGroupByID $parameters)
  {
    return $this->__soapCall('GetNAICSGroupByID', array($parameters));
  }

}
