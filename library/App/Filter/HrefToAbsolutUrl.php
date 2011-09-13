<?php
//require_once 'Zend/Filter/Interface.php';

/**
 * Преобразует атрибут href тега a полученный краулером 
 * в абсолютный url
 */
class App_Filter_HrefToAbsolutUrl implements Zend_Filter_Interface
{   
	protected $_domain  = ''; // домен
	protected $_current = ''; // текущая страница
		
	// устанавливает домен который будет использован с 
	public function setDomain($name)
	{
		$validator = new Zend_Validate_Hostname();
		//echo $name;
		if ($validator->isValid($name)) {				
			$this->_domain = $name;
		} else {
			throw new Exception('Неправильный формат доменного имени');			
		}	
	}
	
	// устанавливает домена который будет использован с 
	public function setPage($uri)
	{		
		if ($this->isValid($uri)) {				
			$this->_page = $uri;
		} else {
			throw new Exception('Неправильный формат страницы');			
		}	
	}
	
	// домен и страница для которой строится ссылк
	public function __construct($domain = null, $page = null) 
	{			
		if ($domain != null)
		    $this->setDomain($domain);
		if ($page != null)
		    $this->setPage($page);
	}
	
	protected function isValid($uri)
	{
		return Zend_Uri::check($uri);
	}
	
    public function filter($href, $skip_external = true)
    {       
    	// если пустое значение
        if (empty($href)) return null;
            	
        // если это абсолютный урл
    	if ($this->isValid($href)) {
	    	// если внешняя ссылка
	        if ($skip_external) {
	        	// если указан чужой домен	        	
	        	$pos = strpos($href, 'http://' . $this->_domain);
	        	if ($pos === false || $pos != 0) 
	        	    return null;
	        }
    		return $href;
    	} 

    	// формируем ссылку из относительного пути
    	if ($href['0'] == '/'){ 
    		// от корня
    		$url = 'http://' . $this->_domain . $href;
    	} else {
    		// от текущей страницы)
    		$url = $this->_page . '/' .$href;
    	}    	    	
    	//echo '$href= ' . $url . ' position = ' . strpos($url, 'http://' . $this->_domain) . '<br />';
    	if ($this->isValid($url)) 
    		return $url; 
    	return null;
    	
  /*  	 
    	// оставляем только символы алфавита и цифры + пробелы(true)
    	$alnum = new Zend_Filter_Alnum(true);
    	$value = $alnum->filter($value);
    	
    	// производим дальнейшие преобразования
    	$filter = new Zend_Filter();
		$filter->addFilter(new  App_Filter_StringToLower())  // преобразуем к нижнему регистру
		       ->addFilter(new Zend_Filter_StringTrim())     // удаляем пробелы справа и слева
		       ->addFilter(new Zend_Filter_StripNewlines());  // удаляем символы перевода строки (при TextArea они есть)		       
		       
		$code = $filter->filter($value); 
		die($code);*/
		//return $code;
    }
}
    	
       