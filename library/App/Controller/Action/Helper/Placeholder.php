<?php
/**
 * Врапер для captureStart(),captureEnd(). 
 * Позволяет отрендерить в произвольном placeholder указанный скрипт вида.
 * $this->_helper->placeholder('sidebar')->render('some_views.phtml', $options);
 */
class App_Controller_Action_Helper_Placeholder extends Zend_Controller_Action_Helper_Abstract
{
	protected $view;
	protected $placeholder;
	
	public function __construct() {
		$this->view = App_Resource::get('view');
	}
	
    public function direct($placeholder = null) {
    	if (empty($placeholder))
    		throw new Exception('Placeholder name can not be empty!');
    	$this->placeholder = $placeholder;
        return $this;
    }
    
    public function render($script) {    	
    	$this->view->placeholder($this->placeholder)->captureStart();
    	    try {
    	    	echo $this->view->render($script);
    	    } catch (Exception $e) {
    	    	$this->view->placeholder($this->placeholder)->captureEnd();
    	    	throw $e;
    	    }
    	    //$this->view->placeholder($this->placeholder)->captureEnd();    	
    	$this->view->placeholder($this->placeholder)->captureEnd();    	
    }
    
    public function addHtml($html)
    {
    	$this->view->placeholder($this->placeholder)->captureStart();
    	    echo $html;    	
    	$this->view->placeholder($this->placeholder)->captureEnd();
    } 

    public function component($name) {
    	$this->view->placeholder($this->placeholder)->captureStart();
    	    include APPLICATION_PATH . '/component/' . $name . '.phtml';
    	$this->view->placeholder($this->placeholder)->captureEnd();
    }
    
    public function renderCache($script, $cache, $prefix) {
    	$this->view->placeholder($this->placeholder)->captureStart();
            try {
    	    	echo $this->view->renderCache($script, $cache, $prefix);
    	    } catch (Exception $e) {
    	    	$this->view->placeholder($this->placeholder)->captureEnd();
    	    	throw $e;
    	    }
    	$this->view->placeholder($this->placeholder)->captureEnd();
    }

    public function partial($script, array $options = null) {    	
    	$this->view->placeholder($this->placeholder)->captureStart();
    	    echo $this->view->partial($script, $options);    	   
    	$this->view->placeholder($this->placeholder)->captureEnd();
    }
}