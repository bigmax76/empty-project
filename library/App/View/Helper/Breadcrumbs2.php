<?php
class App_View_Helper_Breadcrumbs extends Zend_View_Helper_Abstract
{
	public function breadcrumbs(array $parents = null , $section = 'price' )
    {
      	if ($parents == null ) return;
    	$output = '<ul class="breadcrumb-navigation" style="font-weight:bold; font-size:13px">
                         <li>Вернутся на:&nbsp;&nbsp;<a  href="/" >Главная</a></li>
                  ';
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
        $output = $output . '</ul>';
       
       return $output;    	
    }
}
