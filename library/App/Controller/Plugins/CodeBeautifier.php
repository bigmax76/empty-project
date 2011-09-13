<?php
/** 
 * Плагин осуществляет сборку множественных css и js файлов
 * в один файл css и js в соответствии с полученными параметрами
 */
class App_Controller_Plugins_CodeBeautifier extends Zend_Controller_Plugin_Abstract
{
	protected $_sourceDir;
	protected $_targetDir;
	protected $_dev_mode = true; // при true сборка генерируется всякий раз
	
	private $_targetFile = null;
	
	private $_moduleName;
	private $_controllerName;
	private $_actionName;
	private $_layoutName;
	
	public function __construct()
	{
		$this->_sourceDir = APPLICATION_PATH . '/../data/meta';
		$this->_targetDir = APPLICATION_PATH . '/../public';
	}
	
	public function postDispatch(Zend_Controller_Request_Abstract $request)
	{		
	    $this->_moduleName     = $request->getModuleName();
	    $this->_controllerName = $request->getControllerName();
	    $this->_actionName     = $request->getActionName();	    
	    $this->_layoutName     = Zend_Layout::getMvcInstance()->getLayout();	    
	   
	    $target = $this->getTargetFile();	   
	    if ($this->_dev_mode 
	    	|| !file_exists($this->_targetDir . '/css/' . $target . '.css')
	        || !file_exists($this->_targetDir . '/js/'  . $target . '.js'))
	      	$this->generate();	    	
	  
	    $view = App_Resource::get('view');	    
	    $css  = $view->serverUrl() . $view->baseUrl() . '/css/' . $target . '.css';	         
	    $view->headLink()->appendStylesheet($css);
	    $js  = $view->serverUrl() . $view->baseUrl()  . '/js/'  . $target . '.js';	         
	    $view->headScript()->appendFile($js);
	}
	
	protected function getTargetFile()
	{
		if (null === $this->_targetFile){
			$this->_targetFile = 
			      $this->normalizeKey($this->_layoutName)     . '-'
		        . $this->normalizeKey($this->_moduleName)     . '-' 
		        . $this->normalizeKey($this->_controllerName) . '-'
		        . $this->normalizeKey($this->_actionName);
		}
		return $this->_targetFile;
	}
	
	protected function generate()
	{
		$target_css = $this->_targetDir . '/css/' . $this->getTargetFile() . '.css';
		$target_js  = $this->_targetDir . '/js/'  . $this->getTargetFile() . '.js';
		
		// удаляем старые файлы
		file_put_contents ($target_css,'');
		file_put_contents ($target_js,'');    
		
		// подключаем все файлы из корня $source_dir 
		$source_dir = $this->_sourceDir;     
		$dir = new App_Spl_DirectoryIterator($source_dir);
		foreach($dir as $file) {	
			$source = $source_dir . '/' . $file;		
			if ('css' == $file->getExtension()) {
				file_put_contents ($target_css, file_get_contents($source), FILE_APPEND);
			    continue;
			}
		    if ('js' == $file->getExtension()) 
				file_put_contents ($target_js, file_get_contents($source), FILE_APPEND);
		}    
		
		// подключаем файлы для текущего шаблона
		$layouts_css = $source_dir . '/layouts/' . $this->_layoutName . '.css';
		$layouts_js  = $source_dir . '/layouts/' . $this->_layoutName . '.js';
		if (file_exists($layouts_css))
		    file_put_contents ($target_css, file_get_contents($layouts_css),FILE_APPEND);
		if (file_exists($layouts_js))
		    file_put_contents ($target_js,  file_get_contents($layouts_js),FILE_APPEND);
		
		// подключаем файлы для текущего action
		$base = $this->getActionBase();
		$action_css = $base . '.css';
		$action_js  = $base . '.js';		
		if (file_exists($action_css))
		    file_put_contents ($target_css, file_get_contents($action_css),FILE_APPEND);    
        if (file_exists($action_js))
		    file_put_contents ($target_js,  file_get_contents($action_js),FILE_APPEND);    
	}	
	
	protected function getActionBase()
	{
		return $this->_sourceDir
		              . '/modules'
                      . '/' . $this->_moduleName 
					  . '/' . $this->_controllerName 
					  . '/' . $this->_actionName;
	}

    protected function normalizeKey($key) {  
        return str_replace(array('/', '-'), '_', strtolower($key)); 
    }  
	
}