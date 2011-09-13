<?php
class App_Parser_Abstract
{	        
	protected $_elements = array();			
	protected $_response = null;	
	protected $_url      = null;
	protected $_dom      = null;
	protected $_client   = null;	
	protected $_request_manager = null;
			
	public function __construct($url = null) {	
		$this->_url    = $url;
	}	
    
	public function setRequestManager(App_Parser_RequestManager_Abstract $manager) {
		$this->_request_manager = $manager;
	}
	
	public function getRequestManager() {
		return $this->_request_manager;
	}
	
    public function getByUrl($url) {	
    	$this->reset();	        
		$this->_url = $url;
	}

	/**
	 * возвращает value элементов соответствующих селектору
	 */
	public function getValue($selector = null) {		
		$result = array();			
		$elements = $this->getDomElements($selector);			
		foreach ($elements as $element) 					
			$result[] = trim($element->nodeValue);
		
		return $result;		
	}
	
	/**
	 * Возвращает атрибут $name для элементов соответствующих селектору
	 */
	public function getAttrib($name, $selector)
	{
		$result = array();			
		$elements = $this->getDomElements($selector);			
		foreach ($elements as $element) 					
			$result[] = $element->getAttribute($name);		
		
		return $result;	
	}
	
	/**
	 * удаляет элементы соответствующие селектору
	 */
	public function remove($selector)
	{		
		$elements = $this->getDomElements($selector);		
		foreach ($elements as $element) {
			$parent = $element->parentNode;
			$parent->removeChild($element);			 	
		}
	}
	
	/**
	 * удаляет родителя элемента соответствующего селектору 
	 * (иногда нужно)
	 */
	public function removeParent($selector)
	{		
		$elements = $this->getDomElements($selector);		
		foreach ($elements as $element) {
			$parent   = $element->parentNode;
			$grandDad = $parent->parentNode;
			$grandDad->removeChild($parent);			 	
		}
	}
	
	public function combine(Array $options)
	{
		// проверяем, чтобы количество элементов в объеденяемых массивах совпадало 
		$cnt = array();
	    foreach ($options as $key => $value){
			$value_cnt = count($value);
			$cnt[$value_cnt] = $value_cnt;				
		}
		if (count($cnt) > 1)
			throw new App_Parser_Exception('Колличество элементов в объединяемых массивах должно совпадать!');	
		// и получаем это колличество	
		$cnt = array_pop($cnt);	
		
		$result = array();
		for ($i = 0; $i < $cnt; $i++) {
			foreach ($options as $key => $value){
				$result[$i][$key] = $value[$i];
			}
		}	
		return $result;	
	} 
	
	public function getResponse() {
		if (null === $this->_response) {
    		$client = $this->getClient();
		    //$client->setUri($this->_url); 
	    	$this->_response = $client->request();
    	}
    	return $this->_response;
	}
	
	protected function getClient() {
		if (null === $this->_client) {
			$options = array(
	    		'maxredirects' => 0,
	            'timeout'      => 10,
			//    'keepalive'    => true,
	    	);                 
	       $client = new Zend_Http_Client();
	       $client->setConfig($options);
	       $this->_client = $client;
		}		
	    return $this->_client;
	}
	
	public function setClient(Zend_Http_Client $client){
		$this->_client = $client;
	}
	

    public function getHtml() {
    	return $this->getResponse()->getBody();	       	
    }
    
    protected function getStatus() {    	
    	return $this->getResponse()->getStatus();	
    }
    
    protected function getDom() {
    	if (null === $this->_dom) {    		
    		$dom = new App_Dom_Query();    		
    	    $dom->setDocument($this->getHtml());     	    
    	    $this->_dom = $dom;    	   
    	}    	
    	return $this->_dom; 
    }

  	/**
	 * Возвращает массив DOMDocument Object элемент соответствующих переданному селектору 
	 */
	protected function getDomElements($selector) {
		if (!isset($this->_elements[$selector])) {
			$this->_elements[$selector] = $this->getDom()->query($selector);
		}
		return $this->_elements[$selector];
	}
    
	/**
	 * Сброс переменных объекта (для повторного использования с другим url)
	 */
	protected function reset()	{
		$this->_url      = null;	
	    $this->_response = null;	
	    $this->_dom      = null;
	    $this->_elements = array();  
	}
}