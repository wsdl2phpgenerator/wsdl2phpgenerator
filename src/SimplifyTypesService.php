<?php


class SimplifyTypesService {
	
	
	/**
	 * @var array list of extended simpleTypes
	 */
	private $extendetSimpleTypes = array();
	
	/**
	 * @var array
	 */
	private $typesArray = array();

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
	public function getSimplifiedTypes(SoapClient $client){
		
		$types = $client->__getTypes();
		
 		$this->makeFullRestrictionList($types);
		
		foreach($types as $typeStr)
		{
			$wsdlNewline = ( strpos( $typeStr, "\r\n" ) ? "\r\n" : "\n" );
			$parts = explode($wsdlNewline, $typeStr);
			$tArr = explode(" ", $parts[0]);
			$restriction = $tArr[0];
			$className = $tArr[1];
		
		   /*
			*
			*
			*
			*/
			 
			//@todo gpali add extended classes
// 		 	if (true === $this->isInSimpleTypeArray($restriction) && 'struct' != $this->extendetSimpleTypes[$restriction] ) {
// 				$restriction = $this->extendetSimpleTypes[$restriction];
// 			} 
// 				$this->addSimpleTypeArray($restriction, $className);

			$restriction = $this->getRootType($restriction);
			/*
			*
			*
			*/
			$numParts = count($parts);
			
			if ($numParts > 1) {
				$complexType = $restriction . ' ' . $className . ' {' . PHP_EOL;
				
				for($i = 1; $i < $numParts - 1; $i++){
					$parts[$i] = trim($parts[$i]);
					list($typename, $name) = explode(" ", substr($parts[$i], 0, strlen($parts[$i])-1) );

					/*
					* gpali change to simplytype
					*/
// 					if ($this->isInSimpleTypeArray($typename) && 'struct' != $this->extendetSimpleTypes[$typename] ) {
// 						$typename = $this->extendetSimpleTypes[$typename];
// 					}
// 					$complexType .= '	' . $typename . ' ' . $name . ';' . PHP_EOL ;
					$complexType .= '	' . $this->getRootType($typename) . ' ' . $name . ';' . PHP_EOL ;
						
				}
				
				$this->typesArray[] =  $complexType . '}';
				
			} else {
				$this->typesArray[] = $restriction . ' ' . $className;
			}
			
			
		}
		


		//@todo dump
		var_dump($this->typesArray);
		
		
		
		
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
	protected function isInSimpleTypeArray($className) {
		return (array_key_exists($className, $this->extendetSimpleTypes))?true:false;
	}
	
	
}

?>