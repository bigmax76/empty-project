<?php
/**
 * Модель парсинга категории в Yahho Answer
 */
abstract class App_Crawler_Model_Abstract implements App_Crawler_Model_Interface
{
	// строка в url соответствующая модели
	protected $_url_reg = null;
	
	// строка в url соответствующая модели
	protected $_url_string = null;	
	
	// 
	protected $_url_string_escape = array(); 	
	
	// селекторы страницы соответствующие элементам модели
	protected $_selectors = array(); 
	
	// Объектная модель документа, который парсится 
	protected $_dom = null; 
	
	// url страницы которая парсится
	protected $_url = null;
	
	// результат парсинга страницы
	protected $_result = null;
	
	// результат парсинга страницы
	protected $_elements = null;
	
	// массив параметров модели
	protected $_options;
	
	// Если установить в true то ссылки ссылки с данных страниц собиратся не будут
	public $_not_crawler_this_page = false; 
	
	
	public function __construct(array $options = array())
	{
		$this->_options = $options;
	}
	
	/**
	 *  Сохранение распарсенных данных 
	 */
	abstract public function save();	
	
	
	/**
	 * Возвращает true если переданный url соответствует модели $url
	 */
	public function isModelUrl($url)
	{
		//App_Output::send('$url = ' . $url);
	    if (null != $this->_url_reg) {
			if (preg_match($this->_url_reg, $url)) {
			    //App_Output::send('Проверка на регулярное выражение пройдена');
				return true;	
			}	
		}
		
		if (null != $this->_url_string) 
		{
			if (!strpos($url, $this->_url_string))
			    return false;
			// если url содержит запрешенные параметры    
			foreach ($this->_url_string_escape as $escape) {
				if (strpos($url, $escape))
			        return false;
			}			   
			return true;
		}		  
	}
	
	public function getResult()
	{
		if (null === $this->_result) {
			$result = array();
			$object = array();
			foreach ($this->_selectors as $key => $selector) {
				$elements = $this->getDom()->query($selector);
				//echo 'Элементов = ' . count($elements);			
				//$count = count($elements);
				foreach ($elements as $element) {					
					$value = $element->nodeValue;
					$result[$key][] = $value;	
					$object[$key][] = $element;					   
				}		
			}
			$this->_result   = $result;
			$this->_elements = $object;	
					
		}		
		return $this->_result;
	}
	
	public function getElements()
	{
		if (null === $this->_result) 
			$this->getResult();	   
	    return $this->_elements;
	}
	
	
	public function setDom(Zend_Dom_Query $dom)
	{
		$this->_dom = $dom;
		// обнуляем результаты полученные с предыдущего DOM
		$this->_result = null; 
		return $this;
	}
	
	public function getDom()
	{
		return $this->_dom;
	}
	
	public function getUrl()
	{
		return $this->_url ;		 
	}
	
	public function setUrl($url)
	{
		$this->_url = $url;
		return $this;
	}
}