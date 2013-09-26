<?php

class NAICS
{

  /**
   * 
   * @var string $NAICSCode
   * @access public
   */
  public $NAICSCode = null;

  /**
   * 
   * @var string $Title
   * @access public
   */
  public $Title = null;

  /**
   * 
   * @var string $Country
   * @access public
   */
  public $Country = null;

  /**
   * 
   * @var string $IndustryDescription
   * @access public
   */
  public $IndustryDescription = null;

  /**
   * 
   * @param string $NAICSCode
   * @param string $Title
   * @param string $Country
   * @param string $IndustryDescription
   * @access public
   */
  public function __construct($NAICSCode, $Title, $Country, $IndustryDescription)
  {
    $this->NAICSCode = $NAICSCode;
    $this->Title = $Title;
    $this->Country = $Country;
    $this->IndustryDescription = $IndustryDescription;
  }

}
