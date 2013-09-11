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
	 * @throws Exception
	 * @return boolean
	 */
	public function loadWsdlDom(DOMDocument $dom, Config $config){
		if ("" == $config->getInputXsdDir()) {
			throw new Exception('No xsd-file directory set. Nothing to do.');
			return false;
		}
		
		$this->config = $config;
		$this->xsdDirectoryPath = ('' != $this->xsdDirectoryPath = $this->config->getInputXsdDir() . DIRECTORY_SEPARATOR)
									?$this->config->getInputXsdDir() . DIRECTORY_SEPARATOR
									:'';
		
		
		foreach ($dom->getElementsByTagName("import") as $xsd) {
			$document = $xsd->getAttribute('schemaLocation');
			$this->xsdDocumentList[] = $document;
			$this->searchXsdDocumentLocation($document);
		}
		$this->scanAllXsd();
		var_dump($this->elementList);
		return true;
	}
	
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
	
	private function scanAllXsd(){
		foreach ($this->xsdDocumentList as $index => $document) {
			$this->scanXsd($document);
		}
	}
	
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
					$aChoice[] = $element->getAttribute('name');
				}
				if (0 < count($aChoice) ) {
					$aComplexType['CHOICE'] = $aChoice;
				}
			}
			
			// enumeration
			$aEnum = array();
			foreach ($tag->getElementsByTagName('enumeration') as $node){
				$aEnum[] = $node->getAttribute('value');
			}
			if (0 < count($aEnum) ) {
				$aComplexType['ENUM'] = $aEnum;
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
				$aEnum[] = $node->getAttribute('value');
			}
			if (0 < count($aEnum) ){
				$this->elementList[$tag->getAttribute('name')] = array('ENUM' => $aEnum);
			}
		}
	}

	
	
	
	
	
	
}





































?>