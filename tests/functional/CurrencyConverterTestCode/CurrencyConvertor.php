<?php

include_once('ConversionRate.php');
include_once('ConversionRateResponse.php');


/**
 * 
 */
class CurrencyConvertor extends \SoapClient
{

  /**
   * 
   * @var array $classmap The defined classes
   * @access private
   */
  private static $classmap = array(
    'ConversionRate' => '\\ConversionRate',
    'ConversionRateResponse' => '\\ConversionRateResponse');

  /**
   * 
   * @param array $options A array of config values
   * @param string $wsdl The wsdl file to use
   * @access public
   */
  public function __construct(array $options = array(), $wsdl = 'http://www.webservicex.net/CurrencyConvertor.asmx?WSDL')
  {
    foreach (self::$classmap as $key => $value) {
      if (!isset($options['classmap'][$key])) {
        $options['classmap'][$key] = $value;
      }
    }
    
    parent::__construct($wsdl, $options);
  }

  /**
   * <br><b>Get conversion rate from one currency to another currency <b><br><p><b><font color='#000080' size='1' face='Verdana'><u>Differenct currency Code and Names around the world</u></font></b></p><blockquote><p><font face='Verdana' size='1'>AFA-Afghanistan Afghani<br>ALL-Albanian Lek<br>DZD-Algerian Dinar<br>ARS-Argentine Peso<br>AWG-Aruba Florin<br>AUD-Australian Dollar<br>BSD-Bahamian Dollar<br>BHD-Bahraini Dinar<br>BDT-Bangladesh Taka<br>BBD-Barbados Dollar<br>BZD-Belize Dollar<br>BMD-Bermuda Dollar<br>BTN-Bhutan Ngultrum<br>BOB-Bolivian Boliviano<br>BWP-Botswana Pula<br>BRL-Brazilian Real<br>GBP-British Pound<br>BND-Brunei Dollar<br>BIF-Burundi Franc<br>XOF-CFA Franc (BCEAO)<br>XAF-CFA Franc (BEAC)<br>KHR-Cambodia Riel<br>CAD-Canadian Dollar<br>CVE-Cape Verde Escudo<br>KYD-Cayman Islands Dollar<br>CLP-Chilean Peso<br>CNY-Chinese Yuan<br>COP-Colombian Peso<br>KMF-Comoros Franc<br>CRC-Costa Rica Colon<br>HRK-Croatian Kuna<br>CUP-Cuban Peso<br>CYP-Cyprus Pound<br>CZK-Czech Koruna<br>DKK-Danish Krone<br>DJF-Dijibouti Franc<br>DOP-Dominican Peso<br>XCD-East Caribbean Dollar<br>EGP-Egyptian Pound<br>SVC-El Salvador Colon<br>EEK-Estonian Kroon<br>ETB-Ethiopian Birr<br>EUR-Euro<br>FKP-Falkland Islands Pound<br>GMD-Gambian Dalasi<br>GHC-Ghanian Cedi<br>GIP-Gibraltar Pound<br>XAU-Gold Ounces<br>GTQ-Guatemala Quetzal<br>GNF-Guinea Franc<br>GYD-Guyana Dollar<br>HTG-Haiti Gourde<br>HNL-Honduras Lempira<br>HKD-Hong Kong Dollar<br>HUF-Hungarian Forint<br>ISK-Iceland Krona<br>INR-Indian Rupee<br>IDR-Indonesian Rupiah<br>IQD-Iraqi Dinar<br>ILS-Israeli Shekel<br>JMD-Jamaican Dollar<br>JPY-Japanese Yen<br>JOD-Jordanian Dinar<br>KZT-Kazakhstan Tenge<br>KES-Kenyan Shilling<br>KRW-Korean Won<br>KWD-Kuwaiti Dinar<br>LAK-Lao Kip<br>LVL-Latvian Lat<br>LBP-Lebanese Pound<br>LSL-Lesotho Loti<br>LRD-Liberian Dollar<br>LYD-Libyan Dinar<br>LTL-Lithuanian Lita<br>MOP-Macau Pataca<br>MKD-Macedonian Denar<br>MGF-Malagasy Franc<br>MWK-Malawi Kwacha<br>MYR-Malaysian Ringgit<br>MVR-Maldives Rufiyaa<br>MTL-Maltese Lira<br>MRO-Mauritania Ougulya<br>MUR-Mauritius Rupee<br>MXN-Mexican Peso<br>MDL-Moldovan Leu<br>MNT-Mongolian Tugrik<br>MAD-Moroccan Dirham<br>MZM-Mozambique Metical<br>MMK-Myanmar Kyat<br>NAD-Namibian Dollar<br>NPR-Nepalese Rupee<br>ANG-Neth Antilles Guilder<br>NZD-New Zealand Dollar<br>NIO-Nicaragua Cordoba<br>NGN-Nigerian Naira<br>KPW-North Korean Won<br>NOK-Norwegian Krone<br>OMR-Omani Rial<br>XPF-Pacific Franc<br>PKR-Pakistani Rupee<br>XPD-Palladium Ounces<br>PAB-Panama Balboa<br>PGK-Papua New Guinea Kina<br>PYG-Paraguayan Guarani<br>PEN-Peruvian Nuevo Sol<br>PHP-Philippine Peso<br>XPT-Platinum Ounces<br>PLN-Polish Zloty<br>QAR-Qatar Rial<br>ROL-Romanian Leu<br>RUB-Russian Rouble<br>WST-Samoa Tala<br>STD-Sao Tome Dobra<br>SAR-Saudi Arabian Riyal<br>SCR-Seychelles Rupee<br>SLL-Sierra Leone Leone<br>XAG-Silver Ounces<br>SGD-Singapore Dollar<br>SKK-Slovak Koruna<br>SIT-Slovenian Tolar<br>SBD-Solomon Islands Dollar<br>SOS-Somali Shilling<br>ZAR-South African Rand<br>LKR-Sri Lanka Rupee<br>SHP-St Helena Pound<br>SDD-Sudanese Dinar<br>SRG-Surinam Guilder<br>SZL-Swaziland Lilageni<br>SEK-Swedish Krona<br>TRY-Turkey Lira<br>CHF-Swiss Franc<br>SYP-Syrian Pound<br>TWD-Taiwan Dollar<br>TZS-Tanzanian Shilling<br>THB-Thai Baht<br>TOP-Tonga Pa'anga<br>TTD-Trinidad&amp;amp;Tobago Dollar<br>TND-Tunisian Dinar<br>TRL-Turkish Lira<br>USD-U.S. Dollar<br>AED-UAE Dirham<br>UGX-Ugandan Shilling<br>UAH-Ukraine Hryvnia<br>UYU-Uruguayan New Peso<br>VUV-Vanuatu Vatu<br>VEB-Venezuelan Bolivar<br>VND-Vietnam Dong<br>YER-Yemen Riyal<br>YUM-Yugoslav Dinar<br>ZMK-Zambian Kwacha<br>ZWD-Zimbabwe Dollar</font></p></blockquote>
   * 
   * @param ConversionRate $parameters
   * @access public
   * @return ConversionRateResponse
   */
  public function ConversionRate(ConversionRate $parameters)
  {
    return $this->__soapCall('ConversionRate', array($parameters));
  }

}
