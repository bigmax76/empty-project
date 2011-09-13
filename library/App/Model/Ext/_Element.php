<?php
/**
 * Класс определяющий общие базовые свойства 
 * и поведение элементов всех моделей приложения
 * имеющих древовидную организацию
 * (в потомках необходимо реализовать только специфическое для них поведение)
 * @author таргет
 */
abstract class App_Model_Abstract_CategoryElement extends App_Model_Abstract
{
	// базовые свойства элемента модели приложения
	protected $_id;                  // id элемента(категории)
	protected $_code;                // символьный код (транслит)
	protected $_name;                // имя элемента(категориии)		
	protected $_parent_id;           // id родительской категории
	protected $_active;              // активность 	
	protected $_sort;                // индекс сортировки
	protected $_created_ts;          // время создания
	protected $_modified_ts;         // время последней модификации
		
	// пользовательская SEO информация 
	protected $_custom_title;
	protected $_custom_h1;
	protected $_custom_description;
	protected $_custom_keywords;
	
    //////////////////////
    // сеттеры и геттеры  
    //////////////////////
    
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
     * @param unknown_type $code
     * @return unknown_type
     */
    public function setCode($value)
    {
        $this->_code = (string) $value;
        return $this;
    }
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * @param unknown_type $code
     * @return unknown_type
     */
    public function setName($value)
    {
        $this->_name = (string) $value;
        return $this;
    }
    public function getName()
    {
        return $this->_name;
    }
  
    /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setParent_id($value)
    {
        $this->_parent_id = (int)$value;    
        return $this;
    }

    public function getParent_id()
    {
        return $this->_parent_id;
    }
    
    /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setIs_active($value)
    {
        $this->_active = (int)$value;    
        return $this;
    }

    public function getIs_active()
    {
        return $this->_active;
    }
        /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setSort($value)
    {
        $this->_sort = (int)$value;    
        return $this;
    }

    public function getSort()
    {
        return $this->_sort;
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
    public function setModified_ts($value)
    {
        $this->_modified_ts = (string) $value;
        return $this;
    }

    public function getModified_ts()
    {    	
        return $this->_modified_ts;
    }
    
   /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setCustom_title($value)
    {
        $this->_custom_title = (string) $value;
        return $this;
    }

    public function getCustom_title()
    {    	
        return $this->_custom_title;
    }
    
   /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setCustom_h1($value)
    {
        $this->_custom_h1 = (string) $value;
        return $this;
    }

    public function getCustom_h1()
    {    	
        return $this->_custom_h1;
    }
  
   /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setCustom_description($value)
    {
        $this->_custom_description = (string) $value;
        return $this;
    }

    public function getCustom_description()
    {    	
        return $this->_custom_description;
    }
    
   /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setCustom_keywords($value)
    {
        $this->_custom_keywords = (string) $value;
        return $this;
    }

    public function getCustom_keywords()
    {    	
        return $this->_custom_keywords;
    }
}