<?php
//require_once 'Zend/Filter/Interface.php';

/**
 * Фильтр переводящий кирилицу в транслит
 * @author таргет
 *
 */
class App_Filter_Translit implements Zend_Filter_Interface
{
 
   
    public function filter($value)
    {
    	// 2 и более пробела заменяем на один    	
    	$value = preg_replace("/\s{2,}/", " ", $value);   	
        return $this->translit($value);
    }
    
    /**
	 * перевод кирилицы в транслит
	 */
	private function translit($string)
	{
		
		$table = array( 'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 
		                'Д' => 'D', 'Е' => 'E', 'Ё' => 'YO','Ж' => 'ZH', 
		                'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 
		                'Л' => 'L', 'М' => 'M', 'H' => 'N', 'О' => 'O', 
		                'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 
		                'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 
		                'Ч' => 'CH','Ш' => 'SH','Щ' => 'CSH','Ь' => '', 
		                'Ы' => 'Y', 'Ъ' => '',  'Э' => 'E', 'Ю' => 'YU', 
		                'Я' => 'YA', 
		                
		                ' ' => '-',
		                ',' => '',
		                '.' => '',
						';' => '',
		
		                'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 
		                'д' => 'd', 'е' => 'e', 'ё' => 'yo','ж' => 'zh', 
		                'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 
		                'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 
		                'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 
		                'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 
		                'ч' => 'ch', 'ш' => 'sh','щ' => 'csh','ь' => '', 
		                'ы' => 'y',  'ъ' => '',  'э' => 'e', 'ю' => 'yu', 
		                'я' => 'ya', 
		    ); 
		
		$output = str_replace( 
		        array_keys($table), 
		        array_values($table),$string 
		); 		
		return $output; 		
	}

   
}
