<?
class App_View_Helper_Tags extends Zend_View_Helper_Abstract{
	
	public function tags(array $tags = null, $type = 'price')
	{
        $output = '<div style="padding:8px 0;" class="span-7 1border 1showgrid">
				       <h3>' . $this->view->translate->_('Все теги раздела') . ' </h3>
				       <div style="padding:5px 0 0 10px">				        
					       <ul class="tree" style="font-weight:bold; ">';
						       foreach ($tags as $tag)
							   {
							   	   switch ($type) {
									    case 'price': $url = $this->view->url(array('code' => $tag['code'],'page' =>''), 'price-tag');	
									                  $element_cnt = $tag['price_cnt'];		        	                  
									                  break;
						        	    case 'shop' : $url = $this->view->url(array('code' => $tag['code'],'page' =>''), 'shop-tag');
						        	                  $element_cnt = $tag['shop_cnt'];
						        	                  break;
						        	    case 'ads'  : $url = $this->view->url(array('code' => $tag['code'],'page' =>''), 'ads-tag');
						        	                  $element_cnt = $tag['ads_cnt'];
						        	                  break;				
									    default     : break;
									}
								    if ($element_cnt > 0)
								    {
								    	$output = $output . '<li style="width: 50%; float:left">
													            <a href="' .$url . '">' . $tag['name'] . ' </a>
													         </li>';
								    }
									else
									{
										$output = $output . '<li style="width: 50%; float:left" class="no-active">' 
										                        . $tag['name'] . 
										                    '</li>';
									}
								        
								  							
								}								
				$output = $output . '				
							</ul>	
							<br />					    
						</div>						
					</div>	';	
	    return 	$output;
	}
}
?>