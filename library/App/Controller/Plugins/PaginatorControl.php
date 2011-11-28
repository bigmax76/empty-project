<?php
/** 
 * Плагин слушает Get запросы и при необходимости конфигурирует параметры
 * Zend_Paginator по умолчанию. Также тягает изменненый per_page по сесии 
 */
class App_Controller_Plugins_PaginatorControl extends Zend_Controller_Plugin_Abstract
{
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{		
		$config    = App_Resource::get('config'); 
		$options   = $config->resources->frontController->plugins->PaginatorControl->options;
		
		// получаем данные ini файла 
		$session_ns     = (isset($options->session_namespace))
						? $options->session_namespace
						: 'App';
	    $param_page     = (isset($options->param->page))
						? $options->param->page
						: 'page';
		$param_per_page = (isset($options->param->per_page))
					    ? $options->param->per_page
					    : 'per_page';
	    $default_per_page = (isset($options->default_per_page))
					    ? $options->default_per_page
					    : '10';
		$per_page_range = (isset($options->per_page_range))
					    ? $options->per_page_range
					    : '10, 20, 30';
					    
		// извлекаем данные из сессии			    
		$session = new Zend_Session_Namespace($session_ns);	
		if (!isset($session->per_page))	
			$session->per_page = $default_per_page;			
        $per_page = (int)$session->per_page;       
       
        // извлекаем данные из Get		        
        $page = $request->getParam($param_page, 1);
                
        if ($request->getParam($param_per_page, false)) {        	
        	$per_page    = $request->getParam($param_per_page);
        	$allow_range = $this->getAllowRange($per_page_range);
        	if (!in_array($per_page, $allow_range))
        		$per_page = $default_per_page;
        	$session->per_page = $per_page;
        }
        
        // конфигурируем пагинатор
        App_Paginator::setPage($page);
        App_Paginator::setDefaultItemCountPerPage($per_page);
        Zend_Paginator::setDefaultItemCountPerPage($per_page);    
        
		return;		
	}
	
	protected function getAllowRange($per_page_range)
	{
		$range = explode(",", $per_page_range);
		$result = array();
		foreach ($range as $item)
			$result[] = trim($item);		
		return $result;
	}
}