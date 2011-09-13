<?php
class App_Validate_CodeExists extends Zend_Validate_Db_Abstract  
{
	const CODE_EXIST       = 'codeExist';
	const EMPTY_STRING     = 'emptyString';
	const DUPLICATE_STRING = 'duplicateString';
	
	protected $_messageTemplates = array(
		self::CODE_EXIST   => 'Сообщение об этой ошибке формируется ниже в коде',
		self::EMPTY_STRING => 'Пустая строка не допустима',
		self::DUPLICATE_STRING => 'Имеются дубликаты в TextArea'
	);
	
	public function isValid($value) 
    {  	
    	// преобразуем данные из возможного textarea в массив
    	$codes = explode("\n", $value);   	
    	
    	$textAreaStrings = array(); 
    	//echo '<pre>'; print_r($codes);echo'</pre>';
    	//die();
    	// для каждого введенного тега 
    	foreach ($codes as $code)
    	{
    	    // фильтруем входные данные и преобразуем кирилицу в транслит 
            $filter = new Zend_Filter();
		    $filter->addFilter(new  App_Filter_StringToLower())	
			       ->addFilter(new Zend_Filter_StringTrim())     // удаляем пробелы справа и слева
			       ->addFilter(new Zend_Filter_StripNewlines())  // удаляем символы перевода строки (при TextArea они есть)		       
			       ->addFilter(new App_Filter_Translit());       // лишние внутренние пробелы удаляются внутри App_Filter_Translit 
			$code = $filter->filter($code);      
            
			// пустая строка не допустима
			if ($code == '') {            
	            $this->_error(self::EMPTY_STRING); 	            
	            return false;	            
	        } 
	        
	        // проверка на дубликаты строк в TextArea
	        $exists = in_array($code, $textAreaStrings);
	        if ($exists){
	        	$this->_error(self::DUPLICATE_STRING); 
	        	return false;	
	        }
	        else {
	        	// текущая строка уникальна, сохраняем ее в массиве строк
	        	$textAreaStrings[] = $code;
	        }
	        
	        
	        // проверка на дубликаты в бд
    		$this->_setValue($code);            
	        $result = $this->_query($code); 	       
	        if (!empty($result))  { 
	            $valid = false; 
	            $this->_messageTemplates[self::CODE_EXIST] = 'Уже существует запись с кодом: ' . $code;
	            $this->_error(); 
	            return false;	            
	        } 
    	}        
        return true; 
    } 
}