<?php
class App_Controller_Plugins_LanguageSwitcher extends Zend_Controller_Plugin_Abstract
{
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{		
		$config    = Zend_Registry::get('config'); 
		$options   = $config->resources->frontController->plugins->LanguageSwitcher->options;
	    $session   = new Zend_Session_Namespace('App');	    
	    $translate = App_Resource::get('translate');
	    
	    if ($request->isPost() && $options->method == 'post') {
			$post = $request->getPost();
			//echo '<pre>$post'; print_r($post); echo '</pre>';
			if (isset($post[$options->param])) {
				$locale = $post[$options->param];				
				$translate->setLocale($locale);				
				$session->locale = $locale;				
			}						
		}
		if (isset($session->locale))
			$translate->setLocale($session->locale);
			 
		// Todo дописать установки локали по умолчанию для сайта			
		Zend_Registry::set('locale',$session->locale);	
		defined('PREFIX_DB')
           || define('PREFIX_DB' , Model_Site_Service::getCurrent()->prefix_db);
		defined('LANG_ID')
           || define('LANG_ID'   , $translate->getLocale());
		defined('SITE_LANG')
           || define('SITE_LANG' , PREFIX_DB . LANG_ID);
		return;		
	}
}