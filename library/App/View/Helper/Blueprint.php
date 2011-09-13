<?php
/**
 * Хелпер производящий подключение библиотеки blueprint css
  */
class App_View_Helper_Blueprint extends Zend_View_Helper_Abstract
{	
	public function blueprint()
	{	
		$this->view->headLink()->prependStylesheet(BASE_URL . '/public/external/blueprint/print.css','print');
		$this->view->headLink()->prependStylesheet(BASE_URL . '/public/external/blueprint/screen.css','screen, projection');
		$this->view->headLink()->prependStylesheet(BASE_URL . '/public/external/blueprint/ie.css','screen, projection', 'IE');					
	}
	
}