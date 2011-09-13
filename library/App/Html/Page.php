<?php
class App_Html_Page
{	        
	protected $_html;
	protected $_dom;
	protected $_elements = array();
				
	public function __construct($content) {
		if ($content instanceof Zend_Http_Response)
			$content = $content->getBody();
		$this->setHtml($content);
	}	
    
    public function getHtml() {
    	return $this->_html;	       	
    }
    
    public function setHtml($html)
    {
    	$this->_html = $html;
    	return $this;
    }

    public function __toString()
    {
    	return $this->_html;
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
			throw new App_Html_Exception('Колличество элементов в объединяемых массивах должно совпадать!');	
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
	 * Сброс переменных объекта (для повторного использования с другим html)
	 */
	protected function reset()	{
		$this->_html     = null;		   
	    $this->_dom      = null;
	    $this->_elements = array();  
	}

}