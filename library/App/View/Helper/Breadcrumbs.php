<?php
class App_View_Helper_Breadcrumbs extends Zend_View_Helper_Abstract
{
	public function breadcrumbs(array $parents = null , $section = 'price' )
    {
      	  	
      	
    	$output = '<ul class="breadcrumb-navigation" style="font-weight:bold; font-size:13px">
                         <li><a  href="/" >Главная</a></li>';

    	switch ($section) 
    	{	
    		case 'price': $url = $this->view->url(array('page'=>''), 'price-index');
			              $title = 'Прайс листы';
	                      break;
	        case 'shop' : $url = $this->view->url(array('page'=>''), 'shop-index');
	                      $title = 'Магазины и Фирмы';
	                      break;
	        case 'ads'  : $url = $this->view->url(array('page'=>''), 'ads-index');
	                      $title = 'Oбъявления';
	                      break;				
			default     : break;
		}	
    	$output = $output . '<span>&nbsp;&gt;&nbsp;</span>';
		$output = $output . ' <li><a href="'. $url . '" title="' . $title . '">' . $title . '</a></li>';
    		
                         	       
                
    	if ($parents != null )     	
    	{
	    	foreach ($parents as $node)
	        {    
	        	switch ($section) {
				    case 'price': $url = $this->view->url(array('code' => $node['code']), 'price-category');
	        	                  break;
	        	    case 'shop' : $url = $this->view->url(array('code' => $node['code']), 'shop-category');
	        	                  break;
	        	    case 'ads'  : $url = $this->view->url(array('code' => $node['code']), 'ads-category');
	        	                  break;				
				    default     : break;
				}	
	        	
	        	$output = $output . '<span>&nbsp;&gt;&nbsp;</span>';
	        	$output = $output . ' <li><a href="'. $url . '" title="' . $node['name'] . '">' . $node['name'] . '</a></li>';
	        }
    	} 
    	else
    	{
    		$output = '<ul class="breadcrumb-navigation" style="font-weight:bold; font-size:13px">
                         <li><a  href="/" >Главная</a></li>
                  ';
    	
    		switch ($section) {
				    case 'price': $url = $this->view->url(array('page'=>''), 'price-index');
				                  $title = 'Прайс листы';
	        	                  break;
	        	    case 'shop' : $url = $this->view->url(array('page'=>''), 'shop-index');
	        	                  $title = 'Магазины и Фирмы';
	        	                  break;
	        	    case 'ads'  : $url = $this->view->url(array('page'=>''), 'ads-index');
	        	                  $title = 'Oбъявления';
	        	                  break;				
				    default     : break;
			}	
			//$output = 'Все объявления';
			$output = $output . '<span>&nbsp;&gt;&nbsp;</span>';
			$output = $output . ' <li><a href="'. $url . '" title="' . $title . '">' . $title . '</a></li>';
    		
    	}   	
      	
    	
        $output = $output . '</ul>';
       
       return $output;    	
    }
}
