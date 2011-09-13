<?php
/**
 * @author таргет
 *
 */
class App_Index_Ads extends App_Index_Abstract 
{	
	protected $_indexDir = '/data/index/ads';	   
    
    /**
     * Возвращаем подлежащие индексции элементы
     */
    protected function getElements()
    {
    	$elements = Model_AdsService::getList();    	
    	return $elements;
    }
    
	/**
	 * Создание Lucen документа на осниве переданного массива
	 */
	public function getLuceneDoc(array $ads)
    {
    	$doc = new Zend_Search_Lucene_Document();
    	//echo '<pre>'; print_r($ads);echo'</pre>';
    	//die;
    	// формируем текст подлежащий индексации
		// не факт, что сюда нужно подмешивать телефон и имя фирмы
		$content = $ads['title'] . ' ' . $ads['description'];
		// извлекаем из него основы слов (стеминг)
		$content = $this->stem($content);
		//echo '<pre>'; print_r($content); echo'</pre>';		
		
		// Данные поля разбиваются на лексемы и индексируются,
        // но не сохраняются в индексе.
		$doc->addField(Zend_Search_Lucene_Field::Text('contents', $content, 'utf-8'));
			
		// Данные поля не разбиваются на лексемы, но индексируются и полностью сохраняются в индексе.
        // Сохраненные данные поля могут быть получены из индекса.
        //$doc->addField(Zend_Search_Lucene_Field::Keyword('shop_name', $price['shop_name'], 'UTF-8'));
        
		$doc->addField(Zend_Search_Lucene_Field::Keyword('obj_id', $ads['id']));
		        
		// Данные поля не разбиваются на лексемы и не индексируются,
        // но полностью сохраняются в индексе.
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('ads_id',     $ads['id']));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('code',       $ads['code']));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('parent_id',  $ads['parent_id']));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('user_id',    $ads['user_id']));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('title',      $ads['title'],      'UTF-8'));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('description',$ads['description'],'UTF-8'));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('is_top',     $ads['is_top']));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('is_active',  $ads['is_active']));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('is_bright',  $ads['is_bright']));
			
    	return $doc;    	
    }
	
}