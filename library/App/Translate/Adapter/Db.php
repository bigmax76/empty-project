<?php
class App_Translate_Adapter_Db extends Zend_Translate_Adapter
{
	protected function _loadTranslationData($content, $locale, array $options = array())
    {
    	//echo $content . '<br>';
    	//echo $locale . '<br>';
    	//echo '<pre>$options'; print_r($options); echo '</pre>';    	
    	$callback = array($content['class'], $content['method']);
	    $options['locale'] = $locale;
		$this->_data = call_user_func($callback, $options);
        return $this->_data;
    }

    /**
     * returns the adapters name    
     */
    public function toString() {
        return "Db";
    }

}
