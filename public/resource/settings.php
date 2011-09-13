<?php
// Глобальные настройки
define('HTTP_CACHE', true); 
define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../application'));
define('ASSETIC_PATH' , APPLICATION_PATH . '/../library');
define('CACHE_DIR_CSS', APPLICATION_PATH . '/../data/cache/assetic/css');
define('CACHE_DIR_JS' , APPLICATION_PATH . '/../data/cache/assetic/js');
define('CSS_DIR'      , APPLICATION_PATH . '/../data/assetic/css');
define('JS_DIR'       , APPLICATION_PATH . '/../data/assetic/js');

init_assetic();

/////////////////////////////////////////////
// Функции
////////////////////////////////////////////
function init_assetic() 
{
	ini_set('display_errors','On');
	ini_set('date.timezone','Europe/Kiev');
	error_reporting(E_ALL | E_STRICT);
	
	set_include_path(implode(PATH_SEPARATOR, array(   
	    realpath(ASSETIC_PATH),
	    get_include_path(),    
	)));
}

// автозагрузка кдассов
function __autoload($class_name) {
	$class_name = str_replace('\\', '/', $class_name) . '.php';
	require($class_name);
}

function is_not_modified($mtime) {
	if (HTTP_CACHE && isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $mtime == $_SERVER['HTTP_IF_MODIFIED_SINCE'])
		return true;
	return false;
}