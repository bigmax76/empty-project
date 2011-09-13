<?php
/** 
 * Плагин сканирует папку с layout и подключает файл состоящий 
 * из комбинции текущих module name, controler name и  action name 
 * @author bigmax
 */
class App_Controller_Plugins_LayoutSwitcher extends Zend_Controller_Plugin_Abstract
{
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{		
		//var_dump($request);
	    $moduleName     = $request->getModuleName();
	    $controllerName = $request->getControllerName();
	    $actionName     = $request->getActionName();	    
	    $layout_path = Zend_Layout::getMvcInstance()->getLayoutPath();	    
	    
	    $pattern = $moduleName . '/' .	$controllerName . '-' . $actionName;
	    switch ($pattern) 
	    {
		    case 'default/shop-map'  : Zend_Layout::getMvcInstance()->setLayout('default-shop-detail');return;
		        break;	
		    default:
		        break;
		}	    
	    
        if (glob($layout_path . '/' . $pattern . '.phtml')) {			
			Zend_Layout::getMvcInstance()->setLayout($pattern);			
			return; 		    
		}
		
		$pattern = $moduleName . '/' .	$controllerName;
		//die($pattern);
		if (glob($layout_path . '/' . $pattern . '.phtml')) {			
			Zend_Layout::getMvcInstance()->setLayout($pattern);			
			return; 		    
		}
		
	    $pattern = $moduleName;
	    if (glob($layout_path . '/' . $pattern . '.phtml')) {			
			Zend_Layout::getMvcInstance()->setLayout($pattern);			
			return; 		    
		}
				
		Zend_Layout::getMvcInstance()->setLayout('empty');		
		//die($layout_path . '/' . $pattern);		
	}
}