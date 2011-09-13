<?php
//require_once 'Zend/Filter/Interface.php';

/**
 * Преобразует входящe строку так, что каждое слово в строке можно использоватся как тег:
 * - все переводится к нижнему регистру
 * - удаляются лишние пробельные символы
 * - удаляются дубликаты слов
 * - чтобы ограничить колличество тегов на выходе нужно в конструктор передать требуемое колличество
 */
class App_Filter_TagsString implements Zend_Filter_Interface
{
	protected $_max_cnt;
    
	public function __construct($max_cnt = 0) 
	{		
		$this->_max_cnt = (int) $max_cnt;
	} 
   
    public function filter($tags)
    {     
    	$tags = mb_strtolower($tags, 'UTF-8');   	
        $tags = explode(' ', $tags);		
		$tags = array_unique($tags);
		
		$cnt    = 1;
		$result = array();
		foreach ($tags as $tag)
		{
			if (empty($tag)) continue;			
			$result[] = $tag; 
			if ($this->_max_cnt > 0 && $cnt >= $this->_max_cnt) 
			    break;
			$cnt++;
		}
		$result = implode(' ', $result);	
		return $result;
    }
}
    	
       