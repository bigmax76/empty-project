<?php
/**
 * @author таргет
 *
 */
abstract class App_Index_Abstract 
{	
	/**
	 * Папка содержащая поисковый индекс 
	 * (укзывется относительно APPLICATION_PATH )
	 * Задается в потомках
	 * @var unknown_type
	 */
	protected $_indexDir;
	
	/**
	 * путь к папке с файлами поискового индекса
	 * @var unknown_type
	 */
	protected $_indexPath;
		
	protected $_index;
	
	/**
	 * Стеммер (объект извлекающий основу слова)
	 * @var unknown_type
	 */
	protected $_stemmer;
	
	/**
	 * Метод должен вернуть массив элементов подлежащих индексации
	 */
	abstract protected function getElements();
	
	/**
	 * Из переданого элемента метод должен сформировать и вернуть
	 * готовый к индексации Zend_Search_Lucene_Document
	 * (в соответствии с логикой приложения)	 
	 */
	abstract protected function getLuceneDoc($element); 
	
	public function __construct()  
	{
		if ($this->_indexDir == Null) 
			throw new Exception('Не указана папка содержащая поисковый индекс ');
		
		$this->_indexPath = APPLICATION_PATH . $this->_indexDir;
					
		set_time_limit(900);
				
		// устанавливаем кодировку строки запроса (Важно!!!)
		Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('utf-8');
		// устанавливаем  UTF-8 совместимый анализатор текста не чувствительный к регистру
		Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive());
    }
	
    public function getStemmer()
    {
    	//сюда позже можно дабавить выбор стеммера соответствующему нужному языку 
        if (null === $this->_stemmer)  {       	
            $this->_stemmer = new App_Search_Stemmer_Ru();
        }        
        return $this->_stemmer;
    }
    
	/* 
	 * Поиск по запросу
	 * @param $query search query
	 * @return array Zend_Search_Lucene_Search_QueryHit
	 */
	public function find($query)
	{
		try{
			$index = Zend_Search_Lucene::open($this->_indexPath);
		} catch (Zend_Search_Lucene_Exception $e) {
			echo "Ошибка:{$e->getMessage()}";
		}
		
//		$query = $this->parse($query);	    
		
		return $index->find($query);
	}
	
	/**
	 * Парсим запрос (разбиваем на фразы,извлекаем основы слов и т.п.)
	 * Предполгается что поисковые фразы и синонимы разделены запятыми
	 */
	protected function parse($query)
	{				
		$phrase_filter = new App_Filter_Search_Query_Phrase_Ru();
		
		// преобразуем входящюю строку в массив
		$phrases = explode(",", $query); 
		
		$result = $phrase_filter->filter($phrases[0]);
		$cnt = count($phrases);		
		for ( $i = 1; $i < $cnt; $i++)
		{
			$phrase = $phrase_filter->filter($phrases[$i]);
			$result = $result . ' OR ' . $phrase ;
		}
				
		return $result;		
	}

	/**
	 * добавление к индексу отдельного элемента
	 */
	public function add(array $element, $auto_commit = false)
	{
	    //пробуем создать поисковый индекс 
	  	try {
	  		$index = Zend_Search_Lucene::open($this->_indexPath);
	  		$doc = $this->getLuceneDoc($element);				
			$index->addDocument($doc);
			if ($auto_commit)
			    $index->commit();
			//$index->optimize();
	  	} 
	  	catch (Zend_Search_Lucene_Exception $e) {
	  	    echo "<p> Ошибка индексации {$e->getMessage()}</p>";
	  	}		
	}
	
	/**
	 * Удаление из индекса объекта по его id
	 */
	public function delete($id)
	{			
	  	try {
	  		$index = Zend_Search_Lucene::open($this->_indexPath);
		  	
	  		// находим и удаляем старый документ	    	   	
			$hits = $index->find('obj_id:"' . $id . '"');
			//echo '<pre>'; print_r($hits); echo '</pre>';		
			foreach ($hits as $hit) {
				//echo '<pre>$hit'; print_r($hit); echo '</pre>';
			    $index->delete($hit->id);
			}	  		
	  	} 
	  	catch (Zend_Search_Lucene_Exception $e) {
	  	    echo "<p> Ошибка индексации {$e->getMessage()}</p>";
	  	}	
	  
	}
	
    /**
	 * Полная переиндексация элементов индекса
	 * (старый индекс будет удален и создан новый)
	 */
	public function update() {
		//удаляем существующий индекс, в большинстве случае эта операция 
		//с последующий созданием нового индекса работает гораздо быстрее
		$this->recursive_remove_directory($this->_indexPath, TRUE);

		//пробуем создать поисковый индекс 
	  	try {
	  		$index = Zend_Search_Lucene::create($this->_indexPath);
	  	} 
	  	catch (Zend_Search_Lucene_Exception $e) {
	  	    echo "<p> Не удалось создать поисковой индекс: {$e->getMessage()}</p>";
	  	}
        
	  	//начинаем индексацию элементов 
		try
		{
			// счетчик проиндексированных элементов
			$cnt = 0; 
			// получаем массив элементов подлежащих индексации
			$elements = $this->getElements();
					
			foreach ($elements as $element) 
			{
				$doc = $this->getLuceneDoc($element);				
				$index->addDocument($doc);
				$cnt++;
			}
		} 
		catch (Zend_Search_Lucene_Exception $e) 
		{
    		echo "<p class=\"ui-bad-message\">Ошибки индексации: {$e->getMessage()}</p>";
    	}

    	// производим оптимизацию индекса
    	$index->optimize();
        return $cnt; // возвращаем число проиндексированных элементов
	}
	
	
	/**
	 * Разбивает переданный текст на слова и извлекает для каждого его основу
	 * Возвращает строку состоящюю из основ переданных слов, которая и будет в
	 * дальнейшем проиндексирована
	 */
	protected function stem($text)
	{
		//echo '<pre>'; print_r($text);echo'</pre>';
		$stemmer = $this->getStemmer();		
		// разбиваем переданный текст на слова
		preg_match_all('/([a-zA-Zа-яА-Я]+)/ui',$text, $result);
		//echo '<pre>'; print_r($result);echo'</pre>';
		$cnt = count($result[1]);
		$res = '';
        for ($i=0; $i < $cnt ; $i++)
        {
        	//находим основу каждого слова 
        	$base = $stemmer->stem_word($result[1][$i]);
        	$res = $res . ' ' . $base;
        } 
        return $res;
	}
	
	/**
	 * recursive_remove_directory( directory to delete, empty )
	 * expects path to directory and optional TRUE / FALSE to empty
	 *
	 * @param $directory
	 * @param $empty TRUE - just empty directory
	 */
	function recursive_remove_directory($directory, $empty = false)
	{
		if(substr($directory,-1) == '/')
		{
			$directory = substr($directory,0,-1);
		}
		if(!file_exists($directory) || !is_dir($directory))
		{
			return FALSE;
		}elseif(is_readable($directory))
		{
			$handle = opendir($directory);
			while (FALSE !== ($item = readdir($handle)))
			{
				if($item != '.' && $item != '..')
				{
					$path = $directory.'/'.$item;
					if(is_dir($path))
					{
						$this->recursive_remove_directory($path);
					}else{
						unlink($path);
					}
				}
			}
			closedir($handle);
			if($empty == FALSE)
			{
				if(!rmdir($directory))
				{
					return FALSE;
				}
			}
		}
		return TRUE;
	}
}