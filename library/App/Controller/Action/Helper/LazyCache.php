<?php
/**
 * Хелпер производит отложенное кеширование
 */
class App_Controller_Action_Helper_LazyCache extends Zend_Controller_Action_Helper_Abstract
{
	protected $_cache;
	protected $_cacheId ;	
	protected $_isActual = false;
	protected $_data;
		
	public function _construct()
	{
		//Zend_Controller_Action_HelperBroker::getStack()->offsetSet(-100, $this);    	
	}
	
    public function direct($cache_name)
    {    	
    	$cache = App_Resource::get('cachemanager')->getCache($cache_name);    	  	
        $this->_cache = $cache;
        return $this;
    }
    
    public function load() {    	
    	$result = $this->_cache->load($this->_getCacheId());
    	if ($result)
    	    $this->_isActual = true;    	
    	return $result;
    }
    
    public function save($data) {
    	$this->_data = $data;
    }    
    
    /**
     * В качестве идентификатора кеша используется пормализованный url страницы
     */
    protected function _getCacheId() {
    	if (null === $this->_cacheId) {
    		$cacheId = $this->getActionController()->view->url();
    		$cacheId = $this->_normalizeKey($cacheId);
    		$prefix_db = Model_Site_Service::getCurrent()->getPrefix_db();
    		$cacheId = $prefix_db . '_' . $cacheId;
    		$this->_cacheId = $cacheId;
    	}      	
    	return $this->_cacheId;
    }
    
    public function postDispatch()
    {        	
    	if(!$this->_isActual)
    	    $this->_cache->save($this->_data, $this->_getCacheId());    	
    }
    
    protected function _normalizeKey($key) {                 
        return str_replace(array('/', '-'), '_', strtolower($key));
    }  
   
}