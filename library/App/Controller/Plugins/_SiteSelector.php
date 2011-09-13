<?php
/** 
 * v.alfa
 * Плагин устанавливает префикс базы данных в зависимости от используемого сайта
 */
class App_Controller_Plugins_SiteSelector extends Zend_Controller_Plugin_Abstract
{	
	
	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
	{			
		$prefix_db = Model_Site_Service::getCurrent()->prefix_db;
		Model_Node_Service::setPrefix($prefix_db);
		//Model_Node_Service::setPrefix('app');	
	}
	

}