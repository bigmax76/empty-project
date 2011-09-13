<?php
/**
 * @author таргет
 *
 */
class App_Index_Shop extends App_Index_Abstract 
{	
	protected $_indexDir = '/data/index/shop';	   
    
    /**
     * Возвращаем подлежащие индексции элементы
     */
    protected function getElements()
    {
    	$elements = Model_ShopService::getList();    	
    	return $elements;
    }
    
	/**
	 * Создание Lucen документа на осниве переданного массива
	 */
	protected function getLuceneDoc(array $shop)
    {
    	//echo '<pre>'; print_r($shop); echo '</pre>'; 
    	
    	$doc = new Zend_Search_Lucene_Document();
    	
    	$price = Model_PriceService::getByShop($shop['id']); 
    	if (is_array($price))
    	{
    		$contents = ''; 
	    	foreach ($price as $item) 
	    	{    	
	    		$index_text = $item['body'];         // готовим к индексации текст прайс позиции
	    		$index_text = $this->stem($index_text); // извлекаем основы слов
	      	    $contents = $contents . ' ' . $index_text;
	    	}
	      	// Данные прайс позиции разбиваются на лексемы и индексируются,
	        // но не сохраняются в индексе.
	        $doc->addField(Zend_Search_Lucene_Field::Text('contents', $contents, 'utf-8'));    		
    	}
    			
		/////////////////////////////////////////////////////////////////////////////////////////////
		// Данные поля не разбиваются на лексемы, но индексируются и полностью сохраняются в индексе.
        // Сохраненные данные поля могут быть получены из индекса.
        //$doc->addField(Zend_Search_Lucene_Field::Keyword('shop_name', $price['shop_name'], 'UTF-8'));

		////////////////////////////////////////////////////////////
		// Данные поля не индексируются и не разбиваются на лексемы 
        // но могут быть получены из индекса.        
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('shop_id',  $shop['id']));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('code',     $shop['code']));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('user_id',  $shop['user_id']));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('tariff_id',$shop['tariff_id']));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('name',     $shop['name'],     'UTF-8'));
		//$doc->addField(Zend_Search_Lucene_Field::UnIndexed('full_name',$shop['full_name'],'UTF-8'));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('short_description',$shop['short_description'],'UTF-8'));
		//$doc->addField(Zend_Search_Lucene_Field::UnIndexed('full_description', $shop['full_description'],'UTF-8'));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('address',  $shop['address'],'UTF-8'));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('phone',    $shop['phone'],  'UTF-8'));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('email',    $shop['email'],  'UTF-8'));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('site',     $shop['site'],   'UTF-8'));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('isq',      $shop['isq'],    'UTF-8'));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('skype',    $shop['skype'],  'UTF-8'));
		
		echo '<pre>'; print_r($doc); echo '</pre>'; 
		
    	return $doc;    	
    }
	
}