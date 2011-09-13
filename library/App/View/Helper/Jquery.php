<?php
/**
 * Хелпер производящий подключение библиотеки jquery
 * 
 * вызов без параметров приведет к подключению min версии.
 * для подключения полной версии необходимо передать произвольный параметр - например $this->jquery(1) 
 */
class App_View_Helper_Jquery extends Zend_View_Helper_Abstract
{	
	public function jquery($type = null)
	{	
		// если хелпер был вызван без параметров подключаем min версию
		if (!isset($type))
		{
			$this->view->headScript()->appendFile('/public/external/jquery/jquery.min.js');	
		}
		else
		{
			$this->view->headScript()->appendFile('/public/external/jquery/jquery.js');
		}	
			
	}
	
}