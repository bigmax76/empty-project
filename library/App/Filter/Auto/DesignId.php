<?php
//require_once 'Zend/Filter/Interface.php';

/**
 * Вовращает нужный color_id если находит включение нужного слова
 */
class App_Filter_Auto_DesignId implements Zend_Filter_Interface
{
	protected $params;
	 
    public function __construct()
    {    	
    	//извлекаем массив доступных кодов и их значений  
    	$this->params = Model_Color_Service::getFilter();
    }
   
    // если в название встречается упоминание цвета - возвращаем его код
    public function filter($value)
    {    	
    	foreach($this->params as $key => $words)
    	{
    		$words = explode(',',$words);
    		foreach ($words as $word) {
    			// если подстрока не найдена
    			if (stripos(mb_strtolower($value, 'UTF-8'), mb_strtolower($word, 'UTF-8')) === false) {
    			continue;    			
	    		}    		
	    		return $key;
    		}    		
    	}
    	return 0;		
    }
}       