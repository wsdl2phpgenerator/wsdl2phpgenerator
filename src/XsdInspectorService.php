<?php
require_once dirname(__FILE__).'/Config.php';

/**
 * service class for inheritance, enum, choices in xsd description
 * implemented as singelton helper
 * @uses config.php
 *
 */
class XsdInspectorService {
	
	/**
	 * @var XsdInspectorService singelton instance
	 * 
	 */
	private static $instance = null;
	
	/**
	 * @var Config
	 */
	private $config;
	
	/**
	 * @var string
	 */
	private $xsdDirectoryPath;
	
	/**
	 * array of xsd complexTypes hierarchy
	 * @var array
	 */
	private $elementList = array();
	
	/**
	 * array of xsd document location <string>
	 * @var array
	 */
	private $xsdDocumentList = array();
	
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
	 * load wsdl document and xsd-files for discovering type-inheritance, enumeration and choices
	 * @uses Config.php
	 * @param DOMDocument $dom
	 * @param Config $config
	 * @return boolean
	 */
	public function loadWsdlDom(DOMDocument $dom, Config $config){
		$this->config = $config;
		if ("" == $config->getInputXsdDir()) {
			// use directory from wsdl importfile
			$directory = stristr($config->getInputFile(), substr(strrchr($config->getInputFile(), DIRECTORY_SEPARATOR), 1),true );
			if (strtolower(substr($directory, 0,4)) != 'http') {
				$this->xsdDirectoryPath = $directory;
			}
		} else {
			// xsd directory specified	
			$this->xsdDirectoryPath = ('' != $this->xsdDirectoryPath = $this->config->getInputXsdDir() . DIRECTORY_SEPARATOR)
									?$this->config->getInputXsdDir() . DIRECTORY_SEPARATOR
									:'';
		}
		
		
		foreach ($dom->getElementsByTagName("import") as $xsd) {
			$document = $xsd->getAttribute('schemaLocation');
			$this->xsdDocumentList[] = $document;
			$this->searchXsdDocumentLocation($document);
		}
		$this->scanAllXsd();
		
		return true;
	}
	
	/**
	 * search for all xsd document in wsdl reques/response and child
	 * @param string $document
	 */
	private function searchXsdDocumentLocation($document) {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->xmlStandalone = false;
		$dom->preserveWhiteSpace = false;
		$dom->load( ($this->xsdDirectoryPath . $document) );
		foreach ($dom->getElementsByTagName("include") as $xsd) {
			$document = $xsd->getAttribute('schemaLocation');
			if (!in_array($document, $this->xsdDocumentList)) {
				$this->xsdDocumentList[] = $document;
				$this->searchXsdDocumentLocation($document);
			}
		}
	}
	
	/**
	 * start scanning all xsd document
	 */
	private function scanAllXsd(){
		foreach ($this->xsdDocumentList as $index => $document) {
			$this->scanXsd($document);
		}
	}
	
	/**
	 * @param string $document
	 */
	private function scanXsd($document) {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->xmlStandalone = false;
		$dom->preserveWhiteSpace = false;
		$dom->load( ($this->xsdDirectoryPath . $document) );
		
		
		// complexTypes
		foreach($dom->getElementsByTagName("complexType") as $tag){
			$complexTypeName = $tag->getAttribute('name');
			$aComplexType = array();			
			
			
			// class extension
			foreach ($tag->getElementsByTagName('extension') as $node){
				$aComplexType['EXTENDS'] = $node->getAttribute('base');
			}	
			
			// choice
			foreach ($tag->getElementsByTagName('choice') as $node) {
				$aChoice = array();
				foreach ($node->getElementsByTagName('element') as $element) {
					$aChoice[] = '"' . $element->getAttribute('name') . '"';
				}
				if (0 < count($aChoice) ) {
					$aComplexType['CHOICE'] = $aChoice;
				}
			}
			
			if (0 < count($aComplexType)) {
				$this->elementList[$complexTypeName] = $aComplexType;
			}
		}
		
		
		// simpleTypes
		foreach($dom->getElementsByTagName("simpleType") as $tag){
			
			// enumeration
			$aEnum = array();
			foreach ($tag->getElementsByTagName('enumeration') as $node) {
				$aEnum[] = '"' . $node->getAttribute('value') . '"';
			}
			if (0 < count($aEnum) ){
				$this->elementList[$tag->getAttribute('name')] = array('ENUM' => $aEnum);
			}
		}
	}

	/**
	 * search if element has special properties (inheritance, choices, enumeration)
	 * @param string $elementName
	 * @return boolean
	 */
	public function isInElementList($elementName){
		return (array_key_exists($elementName, $this->elementList));
	}
	
	
	/**
	 * returns basis class (Wsdl2PhpGeneratorBasicClass) or extension class if avail
	 * @param string $className
	 * @return string
	 */
	public function getExtensionClassName($className) {
		if ($this->isInElementList($className)) {
			if (array_key_exists('EXTENDS', $this->elementList[$className])  ) {
				return $this->elementList[$className]['EXTENDS'];
			}
		}
		return 'Wsdl2PhpGeneratorBasicClass';
	}
	
	/**
	 * returns an array of strings if var is enumeration else null
	 * @param string $varType
	 * @return NULL|multitype:string
	 */
	public function getEnumeration($varType) {
		if ($this->isInElementList($varType)) {
			if (array_key_exists('ENUM', $this->elementList[$varType])) {
				return $this->elementList[$varType]['ENUM'];
			};
		}
		return null;
	}
	
	/**
	 * returns an array of strings if class has choice else null
	 * @param string $className
	 * @return NULL|multitype:string
	 */
	public function getChoice($className) {
		if ($this->isInElementList($className)) {
			if (array_key_exists('CHOICE', $this->elementList[$className])) {
				return $this->elementList[$className]['CHOICE'];
			}
		}
		return null;
	}
	
}





































?>