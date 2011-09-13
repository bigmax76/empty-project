<?php
class App_PPage_Invoice_Cash extends App_PPage_Invoice_Abstract
{
	protected $_payment_type = App_PPage_Invoice::CASH_PAYMENT;
	
	protected $_payer_name;	
	protected $_contact_phone;
	protected $_adress;
	
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
    public function setAddress($value)
    {
        $this->_address = (string)$value;    
        return $this;
    }

    public function getAddress()
    {
        return $this->_address;
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
}