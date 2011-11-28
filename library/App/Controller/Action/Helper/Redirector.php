<?php
class App_Controller_Action_Helper_Redirector extends Zend_Controller_Action_Helper_Redirector
{
    public function gotoSimple($action, $controller = null, $module = null, array $params = array())
    {    	
        $this->setGotoSimple($action, $controller, $module, $params);

        if ($this->getExit()) {
            $this->redirectAndExit();
        }
    }
}