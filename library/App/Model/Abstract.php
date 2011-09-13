<?php
/**
 * Абстрактный класс реализующий основные функции 
 * связаные с персистентностью своих потомков
 * @author таргет
 *
 */
abstract class App_Model_Abstract
{   
	protected $_mapper;

    /**
	 * Конструктор объекта модели. Заполняет поля объекта данными из переданного массива.
	 * (CRUD - Create)
	 * @param array $data
	 * @return unknown_type
	 */
	public function __construct($options = null)
    {   
        if (!empty($options)) 
            $this->setOptions($options);
        
    }
    
    /**
     * Меджик метод позволяющий "работать" с protected свойства объекта "напрямую"
     * (например при попытке записать $this->id 
     * будет вызван setId() и записано protected свойство _id)  
     */
    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {
            throw new Exception('Invalid shop property: ' . $name);
        }
        $this->$method($value);
    }
    
    /**
     * Меджик метод позволяющий "работать" с protected свойства объекта "напрямую"
     * (например при попытке извлечь $this->id 
     * будет вызван getId() и получено protected свойство _id)  
     */
    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method)) {        	
            throw new Exception('Invalid model property: ' . $name);
        }
        return $this->$method();
    }
   
    /**
     * Заполняет поля объекта данными из массива
     * функция в цикле добавляет приставку "set" к ключам массива
     * и выполняет метод с полученным таким образом именем.
     * Таким образом ключи переданного массива должны соответствовать именам полей объекта
     * и должен существовать соответствующий сетер для этого поля
     * @param array $options
     * @return unknown
     */
    public function setOptions(array $data) {
        $methods = get_class_methods($this);
        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }   

    /**
     * Функция позволяет задать маппер отличный от используемого по умолчанию 
     * (по умолчанию будет используется указанный в _mapperName) 
     * @param unknown_type $mapper
     * @return unknown
     */
    public function setMapper(App_Model_Abstract_Mapper $mapper) {
        $this->_mapper = $mapper;
        return $this;
    }

    /**
     * Функция возвращает текущий маппер для объекта 
     * если маппер не задан функцией setMapper() - она вернет указанный в _mapperName  
     * @param unknown_type $mapper
     * @return unknown
     */
    public function getMapper1() {
    	if (is_object(static::$_mapper)) {
    	    echo 'уже объект 11111111111111111111111111111111111111';
    	} else {
    		echo 'еще не объект - 2222222222222222222222222222222222';
    	}
        if (is_string(static::$_mapper)) {
	        static::$_mapper = App_Resource_Container::getClass(static::$_mapper);
        }
        if (empty(static::$_mapper))
        	throw new Exception('Не задан маппер для модели'); 
        
        return static::$_mapper;
    }
    
    public function getMapper() {
        if (is_string($this->_mapper)) {	        
	        $this->_mapper = App_Resource_Container::getClass($this->_mapper);
        }
        return $this->_mapper;
    }    
    
    /**
     * Возвращает объект по его id
     * @param unknown_type $id
     * @return unknown
     */
    public function getById($id) {
        $this->getMapper()->getById($id, $this);
        return $this;
    }

    /**
     * Возвращает объект по его коду
     * @param unknown_type $code
     * @return unknown
     */
    public function getByCode($code) {
    	$this->getMapper()->getByCode($code, $this);
    	return $this;
    }
    
    public function getByField($field, $value = '') {
     	$this->getMapper()->getByField($field, $value, $this);
    	return $this;
    }
   
    public function getByRange1(array $range, $field = 'id') {
     	return $this->getMapper()->getByRange($range, $field);
    }
    
    public static function getByRange(array $range, $field = 'id') {
    	$mapper = self::getMapper();    	
     	return self::getMapper()->getByRange($range, $field);
    }
    
    /**
     * сохраняет объект и возвращает id новой или измененной записи
     * @return unknown_type
     */
    public function save() {
        $this->beforeSave();
        $id = $this->getMapper()->save($this);
        $this->afterSave();
        return $id;
    }
    
    public function delete() {
		return $this->getMapper()->delete($this->id);
	}

	public function toArray() {
		return $this->getMapper()->toArray($this);
	}

    public function __toString() {
    	$dump = print_r($this->toArray(), true);    	
	    return '<pre>' . $dump . '</pre>';
    }

    public function __isset($name) {
    	// Функция empty() вначале вызывает __isset(), 
        // и только если она возвращает true следует вызов __get().
    	$getter = 'get' . ucfirst($name);
        return method_exists($this, $getter) && !is_null($this->$getter());
    }
    
	protected function beforeSave(){}
    protected function afterSave() {}
          
}