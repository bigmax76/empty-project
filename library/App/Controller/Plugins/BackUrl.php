<?php
/** 
 * Плагин хранит в сессии стек url 10 последних посещенных стрениц. 
 * (это позволяет организации "back" функциональности 
 *  для страниц принадлежащим одновременно нескольким категориям)  
 */
class App_Controller_Plugins_BackUrl extends Zend_Controller_Plugin_Abstract
{
	// объект сессии
	static protected $_session = null;
		
	public function __construct() {
	    self::$_session = new Zend_Session_Namespace('app_back_urls');
	}
	
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{	            
		$current_url = App_Resource::get('view')->url();
				
		// создаем в сессии массив посещенных url (если необходимо)  
	    if (!is_array(self::$_session->back_urls)) {
            self::$_session->back_urls = array();
        }          
        
        // игнорируем refresh page
        $url_count = count(self::$_session->back_urls);
        if ($url_count-1 >= 0 && self::$_session->back_urls[$url_count-1] == $current_url) {
        	//echo '<pre>self::$_session->back_urls'; print_r(self::$_session->back_urls); echo '</pre>';
        	return;
        }
        	
        // удаляем последний тупиковый адрес если пользователь вернулся назад
        if ($url_count-2 >= 0 && self::$_session->back_urls[$url_count-2] == $current_url) {
        	array_pop(self::$_session->back_urls);
        	return;
        }
        	
        // сохраняем в сессии текущий url        
        self::$_session->back_urls[] = $current_url;
        
	    // храним не более 10 последних адресов
	    if ($url_count > 10)
	        array_shift(self::$_session->back_urls);	   
	}    
	
	// возвращает url предыдущей посещенной страницы
	public static function getBackUrl() {
		$url_count = count(self::$_session->back_urls);
		if (isset(self::$_session->back_urls[$url_count-2]))
			return self::$_session->back_urls[$url_count-2];
		return;
	}
}