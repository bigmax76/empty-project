<?php
/**
 * Расширение Zend_Application для кеширования config.ini
 */
class App_Application extends Zend_Application
{    
    protected $_configCache;

    public function __construct($environment, $options = null, Zend_Cache_Core $configCache = null)
    {
        $this->_configCache = $configCache;
        parent::__construct($environment, $options);
        
    }

    protected function _cacheId($file) {
        return md5($file . '_' . $this->getEnvironment());
    }
  
    protected function _loadConfig($file)
    {    	
        $suffix = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (
            $this->_configCache === null 
            || $suffix == 'php' 
            || $suffix == 'inc'
        ) { //No need for caching those
            return parent::_loadConfig($file);
        }

        $configMTime    = filemtime($file);        
        $cacheId        = $this->_cacheId($file);
        $cacheLastMTime = $this->_configCache->test($cacheId);
        
        //Valid cache?
        if ($cacheLastMTime !== false && $configMTime < $cacheLastMTime) { 
            return $this->_configCache->load($cacheId, true);
        } else {
            $config = parent::_loadConfig($file);
            $this->_configCache->save($config, $cacheId, array(), null);
            return $config;
        }
    }
}