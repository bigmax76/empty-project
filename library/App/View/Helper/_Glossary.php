<?php
class App_View_Helper_Glossary extends Zend_View_Helper_Abstract
{
	public function Glossary(array $tags)
    {
    	// глоссарий 
    	$char = array( 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К', 
		               'Л','М','H','О','П','Р','С','Т','У','Ф','Х','Ц', 
		               'Ч','Ш','Щ','Э','Ю','Я');
    	
    	foreach ($tags as $tag)
    	{
    		// получаем первую букву названия тега
    		$firstChar = $tag['name'][0];
    		// переводим к верхнему регистру
    		$firstChar = StrToUpper($firstChar);
    		// если по английск 
    		if ($firstChar < 'A')
    		{
    			$output['A-Z'][] = $tag;
    		}
    		else
    		{
    			$output[$firstChar][] = $tag;
    		}    		
    	}
    	//echo $sum;
    	//$output = $sum/100;
    	return $output;
    }
    
    
    
}