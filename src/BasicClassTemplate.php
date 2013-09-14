<?php

/**
 * @package Wsdl2PhpGenerator
 */

/**
 * @see Type
 */
require_once dirname(__FILE__).'/ComplexType.php';

class BasicClassTemplate extends ComplexType {
	
	/**
	 *
	 * @var array The members in the type
	 */
	private $members;
	
	/**
	 * Construct the object
	 *
	 * @param string $name The identifier for the class
	 * @param string $restriction The restriction(datatype) of the values
	 */
	public function __construct($name = 'Wsdl2PhpGeneratorBasicClass') {
		parent::__construct($name, null);
		$this->members = array();
	}
	
	protected function generateClass() {
		
		if ($this->class != null) {
			throw new Exception("The class has already been generated");
		}
		
		$config = Generator::getInstance()->getConfig();
		$this->class = new PhpClass($this->phpIdentifier, $config->getClassExists(), '', null, false, true);
		$this->class->setExtends($config->getBasicClassName());
		foreach (get_class_methods($this) as $key => $methode){
			if ( 'addMethode_' == stristr($methode, substr(strrchr($methode, '_'), 1),true ) ) {
				$this->$methode();
			}
		}
		
		
		
	}
	
	// basicClass methodes

	/**
	 * add magical setter methode
	 */
	final private function addMethode_0() {
		$methodeName = '__set';
		$description = 'magical setter';
		$param1 = 'name';
		$param2 = 'value';
		$return = null;
// 		$return = PhpDocElementFactory::getReturn('unknown', '');
		$param1Comment = PhpDocElementFactory::getVar('string', 'name', 'the var name');
		$param2Comment = PhpDocElementFactory::getVar('unknown', 'value', 'the value');
		
		$use = array('Exception');
		$functionBlock = '	if(method_exists($this, \'set\'.ucfirst($name))){' . PHP_EOL;
		$functionBlock .= '		$this->{\'set\'.ucfirst($name)}($value);' . PHP_EOL;
		$functionBlock .= '	} else {' . PHP_EOL;
		$functionBlock .= '		throw new Exception("Setter not found!");' . PHP_EOL;
		$functionBlock .= '	}' . PHP_EOL;
	
		$this->addClassMethode($methodeName, $use, $functionBlock, $description, $return, $param1, $param1Comment,$param2,$param2Comment);
	}
	

	/**
	 * add magical getter methode
	 */
	final private function addMethode_1() {
		$methodeName = '__get';
		$description = 'magical getter';
		$param1 = 'name';
		$return = PhpDocElementFactory::getReturn('unknown', '');
		
		$use = array('Exception');
		$functionBlock = '	if(method_exists($this, \'get\'.ucfirst($name))){' . PHP_EOL;
		$functionBlock .= '		return $this->{\'get\'.ucfirst($name)}();' . PHP_EOL;
		$functionBlock .= '	} else {' . PHP_EOL;
		$functionBlock .= '		throw new Exception("Getter not found!");' . PHP_EOL;
		$functionBlock .= '	}' . PHP_EOL;

		$this->addClassMethode($methodeName, $use, $functionBlock, $description, $return,$param1);
	}
	
	/**
	 * add unset instance var with name methode
	 */
	final private function addMethode_2() {
		$methodeName = 'unsetVarname';
		$description = 'unset instance var with name';
		$param1 = 'varName';
		$param2 = null;
		$return = null;
		$param1Comment = PhpDocElementFactory::getVar('string', 'varName', '');
		$param2Comment = null;
	
		$use = null;
		$functionBlock = '	unset($this->$varName);' . PHP_EOL;
	
		$this->addClassMethode($methodeName, $use, $functionBlock, $description, $return, $param1, $param1Comment,$param2,$param2Comment);
	}
	
	/**
	 * add unset all instance vars methode
	 */
	final private function addMethode_3() {
		$methodeName = 'unsetAll';
		$description = 'unset all instance vars';
		$param1 = null;
		$param2 = null;
		$return = null;
		$param1Comment = null;
		$param2Comment = null;
	
		$use = null;
		$functionBlock = '	foreach ($this as $key => $value){' . PHP_EOL;
		$functionBlock .= '		unset($this->$key);' . PHP_EOL;
		$functionBlock .= '	}' . PHP_EOL;
	
		$this->addClassMethode($methodeName, $use, $functionBlock, $description, $return, $param1, $param1Comment,$param2,$param2Comment);
	}
	
	/**
	 * add unset all instance vars excluded one by name methode
	 */
	final private function addMethode_4() {
		$methodeName = 'unsetAllOther';
		$description = 'unset all instance vars exclude one by name';
		$param1 = 'varName';
		$param2 = null;
		$return = null;
		$param1Comment = PhpDocElementFactory::getVar('string', 'varName', 'the var name');
		$param2Comment = null;
	
		$use = null;
		$functionBlock = '	foreach ($this as $key => $value){' . PHP_EOL;
		$functionBlock .= '		if ($key != $varName){' . PHP_EOL;
		$functionBlock .= '			unset($this->$key);' . PHP_EOL;
		$functionBlock .= '		}' . PHP_EOL;
		$functionBlock .= '	}' . PHP_EOL;
	
		$this->addClassMethode($methodeName, $use, $functionBlock, $description, $return, $param1, $param1Comment,$param2,$param2Comment);
	}
	
	/**
	 * add unset all instance vars named in array methode
	 */
	final private function addMethode_5() {
		$methodeName = 'unsetAllOtherExcludeNamedVarsInArray';
		$description = 'unset all instance vars exclude the named in array';
		$param1 = 'paramArray';
		$param2 = null;
		$return = null;
		$param1Comment = PhpDocElementFactory::getVar('array', 'paramArray', '');
		$param2Comment = null;
	
		$use = null;
		$functionBlock = '	foreach ($this as $key => $value){' . PHP_EOL;
		$functionBlock .= '		if (! in_array($key, $paramArray)){' . PHP_EOL;
		$functionBlock .= '			unset($this->$key);' . PHP_EOL;
		$functionBlock .= '		}' . PHP_EOL;
		$functionBlock .= '	}' . PHP_EOL;
	
		$this->addClassMethode($methodeName, $use, $functionBlock, $description, $return, $param1, $param1Comment,$param2,$param2Comment);
	}
	/**
	 * add unset all instance vars named in array methode
	 */
	final private function addMethode_6() {
		$methodeName = 'unsetNamedVarsInArray';
		$description = 'unset all instance vars named in array';
		$param1 = 'paramArray';
		$param2 = null;
		$return = null;
		$param1Comment = PhpDocElementFactory::getVar('array', 'paramArray', '');
		$param2Comment = null;
	
		$use = null;
		$functionBlock = '	foreach ($this as $key => $value){' . PHP_EOL;
		$functionBlock .= '		if (in_array($key, $paramArray)){' . PHP_EOL;
		$functionBlock .= '			unset($this->$key);' . PHP_EOL;
		$functionBlock .= '		}' . PHP_EOL;
		$functionBlock .= '	}' . PHP_EOL;
	
		$this->addClassMethode($methodeName, $use, $functionBlock, $description, $return, $param1, $param1Comment,$param2,$param2Comment);
	}
	
	/**
	 * add reset all instance vars methode
	 */
	final private function addMethode_7() {
		$methodeName = 'resetAll';
		$description = 'reset all instance vars';
		$param1 = null;
		$param2 = null;
		$return = null;
		$param1Comment = null;
		$param2Comment = null;
	
		$use = null;
		$functionBlock = '	foreach ($this as $key => $value){' . PHP_EOL;
		$functionBlock .= '		$this->$key= null;' . PHP_EOL;
		$functionBlock .= '	}' . PHP_EOL;
		
		$this->addClassMethode($methodeName, $use, $functionBlock, $description, $return, $param1, $param1Comment,$param2,$param2Comment);
	}
	
	/**
	 * add reset all instance vars exluded one by name methode
	 */
	final private function addMethode_8() {
		$methodeName = 'resetAllOther';
		$description = 'reset all instance vars exluded one by name';
		$param1 = 'varName';
		$param2 = null;
		$return = null;
		$param1Comment = PhpDocElementFactory::getVar('string', 'varName', 'the var name');
		$param2Comment = null;
	
		$use = null;
		$functionBlock = '	foreach ($this as $key => $value){' . PHP_EOL;
		$functionBlock .= '		if ($key != $varName){' . PHP_EOL;
		$functionBlock .= '			$this->$key = null;' . PHP_EOL;
		$functionBlock .= '		}' . PHP_EOL;
		$functionBlock .= '	}' . PHP_EOL;
	
		$this->addClassMethode($methodeName, $use, $functionBlock, $description, $return, $param1, $param1Comment,$param2,$param2Comment);
	}
	
	/**
	 * add reset all instance vars named in array methode
	 */
	final private function addMethode_9() {
		$methodeName = 'resetAllOtherExcludeVarsInArray';
		$description = 'reset all instance vars exclude names in array';
		$param1 = 'paramArray';
		$param2 = null;
		$return = null;
		$param1Comment = PhpDocElementFactory::getVar('array', 'paramArray', '');
		$param2Comment = null;
	
		$use = null;
		$functionBlock = '	foreach ($this as $key => $value){' . PHP_EOL;
		$functionBlock .= '		if (! in_array($key, $paramArray)){' . PHP_EOL;
		$functionBlock .= '			$this->$key = null;' . PHP_EOL;
		$functionBlock .= '		}' . PHP_EOL;
		$functionBlock .= '	}' . PHP_EOL;
	
		$this->addClassMethode($methodeName, $use, $functionBlock, $description, $return, $param1, $param1Comment,$param2,$param2Comment);
	}
	/**
	 * add reset all instance vars named in array methode
	 */
	final private function addMethode_10() {
		$methodeName = 'resetNamedVarsInArray';
		$description = 'reset all instance vars named in array';
		$param1 = 'paramArray';
		$param2 = null;
		$return = null;
		$param1Comment = PhpDocElementFactory::getVar('array', 'paramArray', '');
		$param2Comment = null;
	
		$use = null;
		$functionBlock = '	foreach ($this as $key => $value){' . PHP_EOL;
		$functionBlock .= '		if (in_array($key, $paramArray)){' . PHP_EOL;
		$functionBlock .= '			$this->$key = null;' . PHP_EOL;
		$functionBlock .= '		}' . PHP_EOL;
		$functionBlock .= '	}' . PHP_EOL;
	
		$this->addClassMethode($methodeName, $use, $functionBlock, $description, $return, $param1, $param1Comment,$param2,$param2Comment);
	}
	
	
	/**
	 * add class methode (access public)
	 * @param string $methodeName
	 * @param string $use
	 * @param string $functionBlock
	 * @param string $description
	 * @param string $return
	 * @param string $param1
	 * @param PhpDocElement $param1Comment
	 * @param string $param2
	 * @param PhpDocElement $param2Comment
	 */
	final private function addClassMethode(	$methodeName,
											$use = null, 
											$functionBlock,
											$description = '', 
											$return = null,
											$param1 = null, 
											PhpDocElement $param1Comment = null, 
											$param2 = null, 
											PhpDocElement $param2Comment = null) {
											
		$comment = new PhpDocComment();
		if (null != $return) {
			$comment->setReturn($return);
		}
		if (null != $param1Comment) {
			$comment->setVar( $param1Comment );
		}
		if (null != $param2Comment) {
			$comment->setVar2( $param2Comment );
		}
		$comment->setDescription($description);
		
		$paramStr = '';
		$paramStr .= (null != $param1)?'$'.$param1:'';
		if (null != $param2) {
			$paramStr .= (null != $param1)?', ':'';
			$paramStr .= '$' . $param2;
		}
		$comment->setAccess(PhpDocElementFactory::getPublicAccess());
		
		if (null != $use) {
			foreach ($use as $className){
				$this->class->addUse($className);
			}
		}
		
		$this->class->addFunction(new PhpFunction('public'
				, $methodeName
				, $paramStr
				, $functionBlock
				, $comment));
	}
	
	
	/* (non-PHPdoc)
	 * @see Type::getClass()
	 */
	public function getClass() {
		$this->generateClass();
		return parent::getClass();
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}

?>