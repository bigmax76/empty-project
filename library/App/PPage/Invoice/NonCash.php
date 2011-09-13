<?php
class App_PPage_Invoice_NonCash extends App_PPage_Invoice_Abstract
{
	protected $_payment_type = App_PPage_Invoice::NON_CASH_PAYMENT;
	
	protected $_payer_name;
	protected $_contact_name;
	protected $_contact_phone;
	
	// сетеры и гетеры
	
    /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setPayer_name($value)
    {
        $this->_payer_name = (string)$value;    
        return $this;
    }

    public function getPayer_name()
    {
        return $this->_payer_name;
    }
    
    /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setContact_name($value)
    {
        $this->_contact_name = (string)$value;    
        return $this;
    }

    public function getContact_name()
    {
        return $this->_contact_name;
    }
    
    /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setContact_phone($value)
    {
        $this->_contact_phone = (string)$value;    
        return $this;
    }

    public function getContact_phone()
    {
        return $this->_contact_phone;
    }
    
    
	
	public function save()
	{
		// производим сцепку вспомагательных полей 
		$this->payment_info = '{payer_name:'   . $this->_payer_name     . '}'  
							 .'{contact_name:' . $this->_contact_name   . '}'
							 .'{contact_phone:' . $this->_contact_phone . '}';						 	
		return parent::save();
	}
	
}
