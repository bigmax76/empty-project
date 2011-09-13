<?php
class App_Controller_Action_Helper_App extends Zend_Controller_Action_Helper_Abstract
{
	protected $_view = null;
	
	protected function getView() {
		if (null === $this->_view) {
			$this->_view = App_Resource::get('view');
		}
		return $this->_view;
	}
	
    public function setTitle($value, $autoEscape = false) {  
        $this->getView()->headTitle($value, 'PREPEND' )->setAutoEscape($autoEscape);
    }
    
    public function setDesc($value, $autoEscape = false) {
    	$this->getView()->headMeta()->appendName('description', $value)->setAutoEscape($autoEscape);
    }
    
    public function setKeywords($value, $autoEscape = false) {
    	$this->getView()->headMeta()->appendName('keywords', $value)->setAutoEscape($autoEscape);;
    }
    
    public function setBackUrl() {
    	//$this->getView()->headMeta()->appendName('keywords', $value);
    }
    
}