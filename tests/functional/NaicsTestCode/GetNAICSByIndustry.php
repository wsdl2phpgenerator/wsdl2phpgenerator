<?php

class GetNAICSByIndustry
{

  /**
   * 
   * @var string $IndustryName
   * @access public
   */
  public $IndustryName = null;

  /**
   * 
   * @param string $IndustryName
   * @access public
   */
  public function __construct($IndustryName)
  {
    $this->IndustryName = $IndustryName;
  }

}
