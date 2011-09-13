<?php
/** 
 * Плагин контроля доступа к конкретным модулям контроллерам и действиям 
 * Идентификатор ресурса формируется из комбинции текущих module name, controler name и action name 
 */
class App_Controller_Plugins_Acl extends Zend_Controller_Plugin_Abstract
{	
	// Массив допустимых ролей приложения.
	// Роли не попавшие в этот список будут приведены к guest 
	protected $application_role = array(
		'guest','staff', 'admin',
	);
	
	protected $accessAllow = array(	
	 // 'ресурс'  => array('массив разрешенных ролей'),	 
	    'default' => array('guest', 'staff', 'admin'),	    
	    'admin'   => array('staff', 'admin'),
	    //'my-auth' => array('guest','member', 'admin'),		
	    //'payment' => array('guest','member', 'admin'),
	    //'admin'   => array('admin'),
	);
	
	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
	{			
		if (!$this->isAllow($request)) {
			// перенаправляем на страницу авторизации
			if(Model_User_Service::getCurrent()->isAuthorized()) {
			    $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
	            $redirector->gotoUrl('/');
			}
			$view = App_Resource::get('view');
			$request->setModuleName('default');		
            $request->setControllerName('auth');     // auth
            $request->setActionName('openid-login'); // openid-login
            $request->setParam('back_url',$view->baseUrl() . '/admin');
		}
	}
	
	protected function isAllow($request)
	{
		//var_dump($request);
	    $moduleName     = $request->getModuleName();
	    $controllerName = $request->getControllerName();
	    $actionName     = $request->getActionName();	    
	    
	    $user = Model_User_Service::getCurrent();
	    
	    $role = 'guest';
	    if ($user->is_staff) 
	    	$role = 'staff';
	    if ($user->is_superuser)
	    	$role = 'admin';
	    if(!in_array($role, $this->application_role))
	    	$role = 'guest';	    
	    	
	    $resource = $moduleName . '-' .	$controllerName . '-' . $actionName;	    
	    if (in_array($resource, array_keys($this->accessAllow)))   
	    {
	        $allowRole = $this->accessAllow[$resource];
	        if (in_array($role, $allowRole))
	        	return true;
	    }   
		
		$resource = $moduleName . '-' .	$controllerName;		
		if (in_array($resource, array_keys($this->accessAllow)))   
	    {
	        $allowRole = $this->accessAllow[$resource];
	        if (in_array($role, $allowRole))
	        	return true;	
	    }  
		
	    $resource = $moduleName;	   
	    if (in_array($resource, array_keys($this->accessAllow)))   
	    {
	        $allowRole = $this->accessAllow[$resource];
	        if (in_array($role, $allowRole))
	        	return true;	    	
	    }	    
	    return false;	    
	}
}
