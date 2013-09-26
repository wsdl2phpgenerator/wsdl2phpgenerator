<?php

class NAICSList
{

  /**
   * 
   * @var int $Records
   * @access public
   */
  public $Records = null;

  /**
   * 
   * @var NAICS[] $NAICSData
   * @access public
   */
  public $NAICSData = null;

  /**
   * 
   * @param int $Records
   * @param NAICS[] $NAICSData
   * @access public
   */
  public function __construct($Records, $NAICSData)
  {
    $this->Records = $Records;
    $this->NAICSData = $NAICSData;
  }

}
