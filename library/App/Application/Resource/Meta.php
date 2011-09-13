<?php
class App_Application_Resource_Meta extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
    	$options = $this->getOptions();
    	//echo '<pre>'; print_r($options); echo '</pre>';
    	$this->getBootstrap()->bootstrap('view');  	  
		$view = $this->getBootstrap()->getResource('view');
		
		//echo '<pre>$options'; print_r($options); echo '</pre>';
		//echo '<pre>$$view'; print_r($view); echo '</pre>';
		foreach ($options as $key =>$value) {
			switch ($key) {
				case 'favicon' : $view->headLink(array('rel' => 'shortcut icon', 'href' => $value));
				                 break;
				case 'css'     : foreach($value as $css) 
					                 $view->headLink()->appendStylesheet($css);
				                 break;
				case 'js'      : foreach($value as $js) 
					                 $view->headScript()->appendFile($js);
					             break;	
				case 'title'   : if (isset($value['default']))
					                 $view->headTitle($value['default']);
					             if (isset($value['separator']))
					                 $view->headTitle()->setSeparator($value['separator']);
					             break;
				case 'keywords'   : $view->headMeta()->appendName('keywords', $value); break;
				case 'description': $view->headMeta()->appendName('description', $value); break;       
				                	
				default       : break;
			}			
		}          
    }           
}