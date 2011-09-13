<?php
//require_once 'Zend/Filter/Interface.php';

/**
 * Преобразует входящее выражение в формат соответствующий полю "code" 
 * моделей приложения
 * @author таргет
 *
 */
class App_Filter_CodeFields implements Zend_Filter_Interface
{
 
   
    public function filter($value)
    {
	// заменяем эти символы на пробел - иначе их снесет Alnum
    	$target = array('-', '.', ':', '_', '(', ')');
    	$value = str_replace($target, " ", $value);
    	    	
    	//$value = preg_replace("/-/", " ", $value); 
    	 	
    	// оставляем только символы алфавита и цифры + пробелы(true)
    	$alnum = new Zend_Filter_Alnum(true);
    	$value = $alnum->filter($value);
    	
    	// удаляем лишние пробелы
    	$value = preg_replace("/\s{2,}/", " ", $value);  // 2 и более пробела заменяем на один  
    	
    	// производим дальнейшие преобразования
    	$filter = new Zend_Filter();
		$filter->addFilter(new Zend_Filter_StringToLower('UTF-8'))  // преобразуем к нижнему регистру		       
		       ->addFilter(new Zend_Filter_StringTrim())     // удаляем пробелы справа и слева
		       ->addFilter(new Zend_Filter_StripNewlines())  // удаляем символы перевода строки (при TextArea они есть)		       
		       ->addFilter(new App_Filter_Translit());       // кирилицу переводим в транслит 
		$code = $filter->filter($value); 
		  	
		return $code;
    }
}
    	
       