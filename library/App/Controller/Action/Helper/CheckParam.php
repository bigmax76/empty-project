<?php
/**
 * Хелпере проверяет, чтобы GET переменная $name соответствовала значению $value
 * в противном случае делается редирект на соответствующую страницу
 */
class App_Controller_Action_Helper_CheckParam extends Zend_Controller_Action_Helper_Abstract
{
    public function direct($name, $value, $route)
    {
    	$param = $this->getRequest()->getParam($name);
    	if ($param === $value)
    		return true;   		
    	
    	$this->getRequest()->setParam($name, $value);
    	$params = $this->getRequest()->getParams();    	
    	
    	$view = App_Resource::get('view');
    	$url = $view->url($params, $route);
    	
        $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
        $redirector->setCode(301);
        $redirector->gotoUrl($url);
    }
}