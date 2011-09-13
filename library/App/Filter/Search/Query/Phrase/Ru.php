<?php
//require_once 'Zend/Filter/Interface.php';

/**
 * Преобразует входящее выражение в формат соответствующий 
 * "фразе" для использования в Zend_Search_Lucene
 * @author таргет
 *
 */
class App_Filter_Search_Query_Phrase_Ru implements Zend_Filter_Interface
{
 
   
    public function filter($value)
    {          	
    	$filter = new Zend_Filter();
		$filter->addFilter(new Zend_Filter_Alnum(true))      // оставляем только символы алфавита и цифры + пробелы(true)    	
		       ->addFilter(new  App_Filter_StringToLower())  // преобразуем к нижнему регистру
		       ->addFilter(new Zend_Filter_StringTrim());    // удаляем пробелы справа и слева
		$value = $filter->filter($value);
		      
		$stemmer = new App_Filter_Stemmer_Ru();    
		
		// разбиваем строку на слова
		$words = explode(" ", $value); 
		
		$result = $stemmer->filter($words[0]);		
		$cnt = count($words);
		for ( $i = 1; $i < $cnt; $i++)
		{
			$result = $result . ' ' . $stemmer->filter($words[$i]);
		}
				
		$result = '"' . $result . '"'; 		
		return $result;
    }
}