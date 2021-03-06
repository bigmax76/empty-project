<?php
/**
 * Собирает перданные переменные в placeholder jsVars
 * вывод в скрипте вида осуществляется через помошник $this->jsVars();
 */
class App_Controller_Action_Helper_JsVars extends Zend_Controller_Action_Helper_Abstract
{
    public function direct($key, $value)
    {       
    	$view = App_Resource::get('view');

    	if (is_string($value)) {
    		$string = sprintf("var %s = '%s';\n", $key, addslashes($value));
    		$view->placeholder('jsVars')->append($string);
    		return;
    	}
        
    	if (is_bool($value)) {
    		$value = ($value) ? 'true' : 'false';
    		$string = sprintf("var %s = %s;\n", $key, $value);
    		$view->placeholder('jsVars')->append($string);
    		return;
    	}

	    if (is_object($value) || is_array($value)) {
			$string = sprintf("var %s = %s;\n", $key, json_encode($value));
		    $view->placeholder('jsVars')->append($string);
    		return;
	    }

	    // else floats, ints
        $string = sprintf("var %s = %s;\n", $key, $value);
    	$view->placeholder('jsVars')->append($string);
	    return;
    }
}