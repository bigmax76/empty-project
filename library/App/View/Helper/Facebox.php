<?php
/**
 * Хелпер производящий подключение библиотеки facebox
 * (всплывающие окна в стиле facebook)
 */
class App_View_Helper_Facebox extends Zend_View_Helper_Abstract
{	
	public function facebox()
	{	
        $this->view->headScript()->appendFile('/external/facebox/facebox.js');
	    $this->view->headScript()->appendScript("	    
				    jQuery(document).ready(function($) {
				      $('a[rel*=facebox]').facebox(); 
				    })
	    ");
        $this->view->headLink()->appendStylesheet('/external/facebox/facebox.css','screen, projection');
		
	}
	
}