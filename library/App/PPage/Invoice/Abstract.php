<?php
/**
 * Назначение класса состоит в том, чтобы хранить и обрабатывать информацию
 * о пополнение счетов персональных страниц.
 * В зависимости от способа пополнения мы нуждаемся в том, чтобы хранить 
 * резличный набор контактной информации. Этот вопрос решается наследованием.
 * Однако, чтобы не создавать разные таблицы базы данных для хранения по сути 
 * вспомогательной и контактной информации
 * Производные классы должны использовать для персистентности поля и методы 
 * этого абстрактного класса.
 * Все данные характерные для конкретного метода оплаты должны склеиватся в 
 * одну строку и сохранятся в поле $_payment_info.  
 * @author bigmax
 */
abstract class App_PPage_Invoice_Abstract extends App_Model_Abstract
{
	protected $_mapperName = 'App_PPage_Invoice_AbstractDbMapper';
	
	protected $_id;            // id выписанного счета
	protected $_user_id;       // id пользователя выписавшего счет
	protected $_ppage_id;      // id персональной страницы счет которой будет пополнен
	protected $_amount;        // сумма платежа
	protected $_payment_type;  // тип платежа
	protected $_payment_info;  // контактная информация	 
	protected $_is_paid;       // флаг(yes/no) оплачен ли счет
	protected $_comments;      // произвольные комментарии.	
	protected $_created_ts;    // дата и время выписки счета
	protected $_paid_ts;       // дата и время оплаты счета	
	//protected $_canceled_ts; // дата и время анулирования	
	
	// Сетеры и Гетеры
	
    /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setId($value)
    {
        $this->_id = (int)$value;    
        return $this;
    }

    public function getId()
    {
        return $this->_id;
    }
    
    /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setUser_id($value)
    {
        $this->_user_id = (int)$value;    
        return $this;
    }

    public function getUser_id()
    {
        return $this->_user_id;
    }
    
    /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setPpage_id($value)
    {
        $this->_ppage_id = (int)$value;    
        return $this;
    }

    public function getPpage_id()
    {
        return $this->_ppage_id;
    }
    
    /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setAmount($value)
    {
        $this->_amount = (int)$value;    
        return $this;
    }

    public function getAmount()
    {
        return $this->_amount;
    }
    
    /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setPayment_type($value)
    {
        $this->_payment_type = (int)$value;    
        return $this;
    }

    public function getPayment_type()
    {
        return $this->_payment_type;
    }
    
    /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setPayment_info($value)
    {
        $this->_payment_info = (string)$value;    
        return $this;
    }

    public function getPayment_info()
    {
        return $this->_payment_info;
    }
	
    /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setIs_paid($value)
    {
        $this->_is_paid = (boolean)$value;    
        return $this;
    }

    public function getIs_paid()
    {
        return $this->_is_paid;
    }
    
    /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setComments($value)
    {
        $this->_comments = (string)$value;    
        return $this;
    }

    public function getComments()
    {
        return $this->_comments;
    }
    
    /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setCreated_ts($value)
    {
        $this->_created_ts = (string) $value;
        return $this;
    }

    public function getCreated_ts()
    {    	
        return $this->_created_ts;
    }
    
    /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setPaid_ts($value)
    {
        $this->_paid_ts = (string) $value;
        return $this;
    }

    public function getPaid_ts()
    {    	
        return $this->_paid_ts;
    }
}
