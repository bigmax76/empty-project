<?php
/**
 * @author таргет
 *
 */
class App_Index_Price extends App_Index_Abstract 
{	
	protected $_indexDir = '/data/index/price';	   
    
    /**
     * Возвращаем подлежащие индексции элементы
     */
    protected function getElements()
    {
    	$elements = Model_PriceService::fetchAll();    	
    	return $elements;
    }
    
	/**
	 * Создание Lucen документа на осниве переданного массива
	 */
	protected function getLuceneDoc(array $price)
    {
    	$doc = new Zend_Search_Lucene_Document();
    	
    	// формируем текст подлежащий индексации
		// не факт, что сюда нужно подмешивать телефон и имя фирмы
		$content = $price['body'];// . ' ' . $price['phone'] . ' ' . $price['shop_name'];
		// извлекаем из него основы слов (стеминг)
		$content = $this->stem($content);
		//echo '<pre>'; print_r($content); echo'</pre>';		
		
		// Данные поля разбиваются на лексемы и индексируются,
        // но не сохраняются в индексе.
		$doc->addField(Zend_Search_Lucene_Field::Text('contents', $content, 'utf-8'));
			
		// Данные поля не разбиваются на лексемы, но индексируются и полностью сохраняются в индексе.
        // Сохраненные данные поля могут быть получены из индекса.
        //$doc->addField(Zend_Search_Lucene_Field::Keyword('shop_name', $price['shop_name'], 'UTF-8'));
                
		// Данные поля не разбиваются на лексемы и не индексируются,
        // но полностью сохраняются в индексе.
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('price_id', $price['id']));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('shop_id',  $price['shop_id']));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('shop_code',$price['shop_code'],'UTF-8'));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('body',     $price['body'],     'UTF-8'));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('price',    $price['price'],    'UTF-8'));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('phone',    $price['phone'],    'UTF-8'));
		$doc->addField(Zend_Search_Lucene_Field::UnIndexed('shop_name',$price['shop_name'],'UTF-8'));
			
    	return $doc;    	
    }
	
}