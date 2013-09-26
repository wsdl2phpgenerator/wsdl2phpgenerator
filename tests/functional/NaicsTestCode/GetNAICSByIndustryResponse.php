<?php

class GetNAICSByIndustryResponse
{

  /**
   * 
   * @var boolean $GetNAICSByIndustryResult
   * @access public
   */
  public $GetNAICSByIndustryResult = null;

  /**
   * 
   * @var NAICSList $NAICSData
   * @access public
   */
  public $NAICSData = null;

  /**
   * 
   * @param boolean $GetNAICSByIndustryResult
   * @param NAICSList $NAICSData
   * @access public
   */
  public function __construct($GetNAICSByIndustryResult, $NAICSData)
  {
    $this->GetNAICSByIndustryResult = $GetNAICSByIndustryResult;
    $this->NAICSData = $NAICSData;
  }

}
