<?php
/**
 * Расширение Zend_Db_Table_Rowset
 */
class App_Model_Image_Rowset extends Zend_Db_Table_Rowset_Abstract
{
	protected $_type = array();
	
	public function init() {
		foreach ($this->_data as $image)
	       	$this->_type[$image['type']][] = $image['id'];
	}
	
	public function __get($name) {
		return (isset($this->_type[$name]) ? $this->_type[$name] : null);
	}
	
	/**
	 * Возвращает true если каждой фотке в наборе присвоен тип
	 * (base, interior ...) 
	 * это функция проекта ей сдесь не место 
	 */
	public function is_type_init() {		
		foreach ($this->_type as $key => $val) {
			if (empty($key))
				return false;
		}
		return true;
	}
}