<?php
/**
 * Хелпер бросает 404 not Found если на вход передано пустое значение
 */
class App_Controller_Action_Helper_Check404 extends Zend_Controller_Action_Helper_Abstract
{
    public function direct($value, $message = 'Page not found')
    {
        $throw = false;
        if (is_array($value)) {
            $throw = sizeof($value) ? false : true;
        } elseif(is_bool($value)) {
            $throw = !$value;
        } else {
            $throw = !empty($value) ? false : true;
        }
        
        if ($throw) {
        	$this->getRequest()->setDispatched(false);
            throw new Zend_Controller_Action_Exception($message, 404);
        }
        
        return true;
    }
}