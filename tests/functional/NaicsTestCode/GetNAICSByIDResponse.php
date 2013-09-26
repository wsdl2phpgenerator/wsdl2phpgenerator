<?php

class GetNAICSByIDResponse
{

  /**
   * 
   * @var boolean $GetNAICSByIDResult
   * @access public
   */
  public $GetNAICSByIDResult = null;

  /**
   * 
   * @var NAICSList $NAICSData
   * @access public
   */
  public $NAICSData = null;

  /**
   * 
   * @param boolean $GetNAICSByIDResult
   * @param NAICSList $NAICSData
   * @access public
   */
  public function __construct($GetNAICSByIDResult, $NAICSData)
  {
    $this->GetNAICSByIDResult = $GetNAICSByIDResult;
    $this->NAICSData = $NAICSData;
  }

}
