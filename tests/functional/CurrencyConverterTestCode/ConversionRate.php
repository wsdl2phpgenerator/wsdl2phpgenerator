<?php

class ConversionRate
{

  /**
   * 
   * @var Currency $FromCurrency
   * @access public
   */
  public $FromCurrency = null;

  /**
   * 
   * @var Currency $ToCurrency
   * @access public
   */
  public $ToCurrency = null;

  /**
   * 
   * @param Currency $FromCurrency
   * @param Currency $ToCurrency
   * @access public
   */
  public function __construct($FromCurrency, $ToCurrency)
  {
    $this->FromCurrency = $FromCurrency;
    $this->ToCurrency = $ToCurrency;
  }

}
