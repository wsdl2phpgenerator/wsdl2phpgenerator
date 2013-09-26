<?php

class GetNAICSGroupByIDResponse
{

  /**
   * 
   * @var boolean $GetNAICSGroupByIDResult
   * @access public
   */
  public $GetNAICSGroupByIDResult = null;

  /**
   * 
   * @var NAICSList $NAICSData
   * @access public
   */
  public $NAICSData = null;

  /**
   * 
   * @param boolean $GetNAICSGroupByIDResult
   * @param NAICSList $NAICSData
   * @access public
   */
  public function __construct($GetNAICSGroupByIDResult, $NAICSData)
  {
    $this->GetNAICSGroupByIDResult = $GetNAICSGroupByIDResult;
    $this->NAICSData = $NAICSData;
  }

}
