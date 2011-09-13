<?php   
/**  
* SPL autoloader class  
*   
* @name Autoloader  
* @package Fooup  
* @subpackage Loader  
* @author Andrew Vasiliev (http://web-blog.org.ua/)   
*/   
class App_Loader_Autoloader   
{   
    /**  
    * Files which we must compile  
    *   
    * @var array  
    */   
    private $loadedFiles;     
       
    /**  
    * Options for autoloader  
    *   
    * @var array  
    */   
    protected $_options = array(   
        'includeFile' => '',   
        'namespaces'  => array('Zend','App','Rediska')
    );   
       
    /**  
    * Constructor  
    *   
    * @param array $options  
    * @return Fooup_Loader_Autoloader  
    */   
    public function __construct($options = null) { 
    	$this->setOptions($options);    
    	include_once $ths->_options['includeFile'];
        spl_autoload_register(array($this,'autoload'));
    }   
       
    /**  
    * Sets options array for an autoloader  
    *   
    * @param array $options  
    */   
    public function setOptions(array $options) {   
        if (!is_array($options)) throw new App_Loader_Autoloader_Exception('Invalid options passed to Fooup Autoloader',500);   
        if (isset($options['includeFile']) && mb_strlen($options['includeFile'])>0) $this->_options['includeFile'] = $options['includeFile'];                   
        if (isset($options['namespaces']) && is_array($options['namespaces'])>0) $this->_options['namespaces'] = $options['namespaces'];           
    }   
       
    /**  
    * Autoloads classes and adds files for compilation  
    *   
    * @param string $classname  
    */   
    public function autoload($classname) {     	
    	//echo  $classname . '<br>';
        if (class_exists($classname,false) || interface_exists($classname,false) || $classname == 'App_Loader_Autoloader') 
           return;   
        $filename = str_replace('_','/', $classname).'.php';
        $parts = explode('_',$classname);  
        if ('Model' == $parts['0']) {
		    $filename = str_replace('Model_', '', $classname);
		    $filename = 'models/' . str_replace('_','/', $filename).'.php';		    
	    }   	           
        
        if ($f = @fopen($filename, "r", true)) {   
            fclose($f);   
            $result = include_once($filename);   
            
            if (isset($parts[0]) && in_array($parts[0],$this->_options['namespaces'])) $this->loadedFiles[] = $filename;           
            return $result;           
        }   
        return false;        
    }   
       
    /**  
    * This method compiles new files into our cache file  
    *   
    */   
    public function compile() {   
        if (!count($this->loadedFiles)) return;   
        $outputFile = $this->_options['includeFile'];   
        $fp = fopen($outputFile, "a+");   
        if (flock($fp, LOCK_EX)) {   
            if ($filesize = filesize($outputFile)) {   
                fseek($fp, 0);   
                $currentFile = fread($fp, $filesize);   
            } else $currentFile = '';   
               
            if (!$currentFile) {   
                $appendSource = "<?php\n";   
                $existingClasses = array();   
            } else {   
                $appendSource = '';   
                $existingClasses = $this->getClassesFromSource($currentFile);   
            }   
               
            for ($i = 0; $i < count($this->loadedFiles); $i++) {   
                $filename = $this->loadedFiles[$i];   
                   
                $f = @fopen($filename, "r", true);   
                $fstat = fstat($f);   
                $file = fread($f, $fstat['size']);   
                fclose($f);   
                $classes = $this->getClassesFromSource($file);   
  
                if (!count(array_intersect($existingClasses, $classes))) {   
                    if (strpos($file, '__FILE__') === false) {   
                        $endFile = substr($file, -2) == '?>' ? -2 : null;   
                        $appendSource .= ($endFile === null ? substr($file, 5) : substr($file, 5, -2));   
                    } else {   
                        $filePath = $this->realPath($filename);   
                        if ($filePath) {   
                            $file = str_replace('__FILE__', "'$filePath'", $file);   
                            $endFile = substr($file, -2) == '?>' ? -2 : null;   
                            $appendSource .= ($endFile === null ? substr($file, 5) : substr($file, 5, -2));   
                        }   
                    }   
                } else {   
                    $appendSource = '';   
                    break;   
                }   
            }   
            if ($appendSource) {   
                fseek($fp, 0, SEEK_END);   
                fwrite($fp, $appendSource);   
            }   
            flock($fp, LOCK_UN);   
        }   
        fclose($fp);   
    }   
       
    /**  
    * Retrieves a list of classes in source file  
    *   
    * @param string $source  
    * @return array  
    */   
    public function getClassesFromSource($source) {   
        preg_match_all('{^\s*(class|interface)\s+(.*?)(\s|$)}im', $source, $matches, PREG_PATTERN_ORDER);   
        return $matches[2];   
    }   
       
    /**  
    * Returns full path to needed file  
    *   
    * @param string $relativeFilename  
    * @return string  
    */   
    public function realPath($relativeFilename) {   
        // Check for absolute path   
        if (realpath($relativeFilename) == $relativeFilename) return $relativeFilename;   
           
        // Otherwise, treat as relative path   
        $paths = explode(PATH_SEPARATOR, get_include_path());   
        foreach ($paths as $path) {   
            $path = str_replace('\\', '/', $path);   
            $path = rtrim($path, '/') . '/';   
            $fullpath = realpath($path . $relativeFilename);   
            if ($fullpath) return $fullpath;   
        }   
  
        return false;   
    }   
       
    /**  
    * Destructor  
    *   
    * Here we call the compilation method  
    */   
    public function __destruct() {   
        $this->compile();       
    }   
}  