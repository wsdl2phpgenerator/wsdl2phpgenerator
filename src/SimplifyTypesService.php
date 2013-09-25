<?php


/**
 * service for searching simple data types (simpleTypes)
 * @example stringLength0to32 -> string 
 * @package Wsdl2PhpGenerator
 * @author georg palischek <georg.palischek@it-treuhand.eu>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 *
 */
class SimplifyTypesService {
	
	/**
	 * @var SimplifyTypesService singelton instance
	 *
	 */
	private static $instance = null;
	
	/**
	 * @var array list of extended simpleTypes
	 */
	private $extendetSimpleTypes = array();
	
	/**
	 * @var array
	 */
	private $typesArray = array();

	private function __construct() {}
	
	/**
	 * Initializes the single instance if it hasn't been, and returns it if it has.
	 */
	public static function instance() {
		if( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	
	/**
	 * makes list of types
	 * @param array $types
	 */
	private function makeFullRestrictionList(array $types) {
		
		foreach ($types as $typeStr) {
			$wsdlNewline = ( strpos( $typeStr, "\r\n" ) ? "\r\n" : "\n" );
			$parts = explode($wsdlNewline, $typeStr);
			$tArr = explode(" ", $parts[0]);
			$restriction = $tArr[0];
			$className = $tArr[1];
			$this->addSimpleTypeArray($restriction, $className);
		}
	}
	
	/**
	 * returns the root of class hirarchie for simple types
	 * @param string $type
	 * @return string (simpleType)
	 */
	public function getRootType($type) {
		if (true === $this->isInSimpleTypeArray($type) && 'struct' != $this->extendetSimpleTypes[$type] ) {
				$restriction = $this->extendetSimpleTypes[$type];
				return $this->getRootType($restriction);
			} 
		return $type;
	}
	
	/**
	 * @param SoapClient $client
	 * @return multitype:string
	 */
	public function loadTypes(SoapClient $client){
		
		$types = $client->__getTypes();
		
 		$this->makeFullRestrictionList($types);
		
		foreach($types as $typeStr)
		{
			$wsdlNewline = ( strpos( $typeStr, "\r\n" ) ? "\r\n" : "\n" );
			$parts = explode($wsdlNewline, $typeStr);
			$tArr = explode(" ", $parts[0]);
			$restriction = $tArr[0];
			$className = $tArr[1];
		
			$restriction = $this->getRootType($restriction);
			$numParts = count($parts);
			
			if ($numParts > 1) {
				$complexType = $restriction . ' ' . $className . ' {' . PHP_EOL;
				
				for($i = 1; $i < $numParts - 1; $i++){
					$parts[$i] = trim($parts[$i]);
					list($typename, $name) = explode(" ", substr($parts[$i], 0, strlen($parts[$i])-1) );
					$complexType .= '	' . $this->getRootType($typename) . ' ' . $name . ';' . PHP_EOL ;
				}
				$this->typesArray[] =  $complexType . '}';
				
			} else {
				$this->typesArray[] = $restriction . ' ' . $className;
			}
		}
		return $this->typesArray;
	}

	/**
	 * @return multitype: the $extendetSimpleTypes
	 */
	public function getExtendetSimpleTypes() {
		return $this->extendetSimpleTypes;
	}

	/**
	 * @return multitype: the $typesArray
	 */
	public function getTypesArray() {
		return $this->typesArray;
	}

	// helper for extended simpleTypes
	
	/**
	 * @param string $restriction
	 * @param string $className
	 */
	protected function addSimpleTypeArray($restriction, $className) {
		$this->extendetSimpleTypes[$className] = $restriction;
	}
	
	/**
	 * @param string $className
	 * @return boolean
	 */
	public function isInSimpleTypeArray($className) {
		return (array_key_exists($className, $this->extendetSimpleTypes))?true:false;
	}
}

?>