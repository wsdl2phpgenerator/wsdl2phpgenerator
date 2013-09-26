<?php

class GetNAICSByID
{

  /**
   * 
   * @var string $NAICSCode
   * @access public
   */
  public $NAICSCode = null;

  /**
   * 
   * @param string $NAICSCode
   * @access public
   */
  public function __construct($NAICSCode)
  {
    $this->NAICSCode = $NAICSCode;
  }

}
