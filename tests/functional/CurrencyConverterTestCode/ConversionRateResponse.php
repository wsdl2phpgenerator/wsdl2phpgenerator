<?php

class ConversionRateResponse
{

  /**
   * 
   * @var float $ConversionRateResult
   * @access public
   */
  public $ConversionRateResult = null;

  /**
   * 
   * @param float $ConversionRateResult
   * @access public
   */
  public function __construct($ConversionRateResult)
  {
    $this->ConversionRateResult = $ConversionRateResult;
  }

}
