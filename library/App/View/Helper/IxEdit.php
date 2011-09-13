<?php
class App_View_Helper_IxEdit extends Zend_View_Helper_Abstract
{
	public function ixEdit()	 
    {  
    	$this->view->headScript()->appendFile('/public/js/jquery/jquery-plus-jquery-ui.js');
		$this->view->headScript()->appendFile('/public/js/ixedit/ixedit/ixedit.packed.js');
		$this->view->headLink()->appendStylesheet('/public/js/ixedit/ixedit/ixedit.css');
		$this->view->headLink()->appendStylesheet('/public/js/ixedit/sample-style/ui-sui.css');		
    }
}