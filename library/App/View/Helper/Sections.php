<?
class App_View_Helper_Sections extends Zend_View_Helper_Abstract{
	
	public function sections(array $sections = null, $type = 'price')
	{		
		if ($sections == null) return;
		$output = '<div id="section-section" style="float:left; font-size:13px; margin-bottom:10px">
						<b>Разделы: </b>';
		foreach ($sections as $section)
		{
		    switch ($type) {
			    case 'price': $url = $this->view->url(array('code' => $section['code'],'page' =>''), 'price-category');
        	                  break;
        	    case 'shop' : $url = $this->view->url(array('code' => $section['code'],'page' =>''), 'shop-category');
        	                  break;
        	    case 'ads'  : $url = $this->view->url(array('code' => $section['code'],'page' =>''), 'ads-category');
        	                  break;				
			    default     : break;
			}
			//$url = $this->view->url(array('code' => $section['code'], 'page' =>''), 'price-category');
			$output = $output . '<a href="' . $url  .'">' . $section['name'] . '</a>, ';
		}		
		// удаляем лишнюю запятую
		$last = strrpos($output, ',');
		$output[$last] = '';
		$output = $output . '</div><div style="clear:both"></div>';
		
		return $output;
	}
}
?>