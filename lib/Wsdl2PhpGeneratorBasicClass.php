<?php


class Wsdl2PhpGeneratorBasicClass {

	
   public function __set($name, $value)
   {
       if(method_exists($this, 'set'.ucfirst($name))){
           $this->{'set'.ucfirst($name)}($value);
       } else {
           throw new Exception("Setter not found!");
       }

   }

   public function __get($name)
   {
       if(method_exists($this, 'get'.ucfirst($name))){
           return $this->{'get'.ucfirst($name)}();
       } else {
           throw new Exception("Getter not found!");
       }
   }
	
// 	/**
// 	 * magical setter
// 	 * @param string $name
// 	 * @param unknown $value
// 	 */
// 	public function __set($name, $value) {
// 		if (property_exists($this,$name)) {
// 			$this->$name = $value;
// 		}
// 	}
	
// 	/**
// 	 * magical getter
// 	 * @param string $name
// 	 */
// 	public function __get($name) {
// 		if (property_exists($this,$name)) {
// 			return $this->$name;
// 		}
// 	}
	
	/**
	 * unset instance var with name
	 * @param string $varName
	 */
	public function unsetVarname($varName) {
		unset($this->$varName);
	}
	
	/**
	 * unset all instance vars
	 */
	public function unsetAll() {
		foreach ($this as $key => $value){
			unset($this->$key);
		}
	}
	
	/**
	 * unset all instance vars excluded by name
	 * @param string $varName
	 */
	public function unsetAllOther($varName) {
		foreach ($this as $key => $value){
			if ($key != $varName){
				unset($this->$key);
			}
		}
	}
	
	/**
	 * unset all instance vars named in array
	 * @param array $paramArray
	 */
	public function unsetAllOtherFromArray($paramArray) {
		foreach ($this as $key => $value){
			if (! in_array($key, $paramArray)){
				unset($this->$key);
			}
		}
	}
	
	/**
	 * reset instance all vars
	 */
	public function resetAll() {
		foreach ($this as $key => $value){
			$this->$key= null;
		}
	}
	
	/**
	 * reset all instance vars exluded on by name
	 * @param string $varName
	 */
	public function resetAllOther($varName) {
		foreach ($this as $key => $value){
			if ($key != $varName){
				$this->$key = null;
			}
		}
	}
	
	/**
	 * reset all instance vars named in array
	 * @param array $paramArray
	 */
	public function resetAllOtherFromArray($paramArray) {
		foreach ($this as $key => $value){
			if (! in_array($key, $paramArray)){
				$this->$key = null;
			}
		}
	}
	
	/**
	 * clone
	 */
	public function __clone(){
		foreach($this as $name => $value){
			if(gettype($value)=='object'){
				$this->$name = clone($this->$name);
			}
		}
	}
	
}

?>