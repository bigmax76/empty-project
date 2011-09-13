<?php
/**
 * Назначение валидатора - возвращать простое сообщение об ошибке 
 * вместо нескольких у стандартного EmailAddress
 */
class App_Validate_EmailAddressSimple extends Zend_Validate_Abstract  
{
	const WRONG_EMAIL       = 'wrongEmail';	
	
	protected $_messageTemplates = array(
		self::WRONG_EMAIL   => 'Неправильный Email',		
	);
	
	public function isValid($value) 
    {  	
    	$validator = new Zend_Validate_EmailAddress();
		if (!$validator->isValid($value)) 
		{
		    $this->_error(self::WRONG_EMAIL); 
		    return false;
		}    	
        return true; 
    } 
}