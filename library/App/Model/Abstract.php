<?php
/**
 * Абстрактный класс реализующий основные функции 
 * связаные с персистентностью своих потомков
 * @author таргет
 *
 */
abstract class App_Model_Abstract
{   
	protected static $_mapper;
	 
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

    public function __isset($name) {
    	// Функция empty() вначале вызывает __isset(), 
        // и только если она возвращает true следует вызов __get().
    	$getter = 'get' . ucfirst($name);
        return method_exists($this, $getter) && !is_null($this->$getter());
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
    public function setOptions($data) {
    	if ($data instanceof Zend_Db_Table_Row)
    	    $data->toArray();
    	
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
    public function setMapper($mapper) 
    {
        if (is_string($mapper))
            $mapper = new $mapper();
        if (!$mapper instanceof App_Model_Abstract_Mapper)
            throw new Exception('Invalid mapper data gateway provided');
        static::$_mapper = $mapper;
        return $this;
    }

    /**
     * Функция возвращает текущий маппер для объекта 
     * если маппер не задан функцией setMapper() - она вернет указанный в _mapperName  
     * @param unknown_type $mapper
     * @return unknown
     */
    public function getMapper() {
        if (is_string(static::$_mapper)) { 
	        static::$_mapper = new static::$_mapper();           
        }
        return static::$_mapper;
    }
        
    /**
     * Возвращает объект по его id
     * @param int $id
     * @return self
     */
    public function getById($id) {
        $this->getMapper()->getById($id, $this);
        return $this;
    }

	/**
     * Возвращает объект по unique key
     * @param array $key
     * @return self
     */
	public function getByPK($key) {
        $this->getMapper()->getByPK($key, $this);
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
    
    public static function getByRange(array $range = array(), $field = 'id') {
     	return self::getMapper()->getByRange($range, $field);
    }
    
    public function getByRange1(array $range, $field = 'id') {
     	return $this->getMapper()->getByRange($range, $field);
    }
    
    /**
     * сохраняет объект и возвращает id новой или измененной записи
     * @return unknown_type
     */
    public function save() {
        return $this->getMapper()->save($this);
    }
    
    public function delete() {
		return $this->getMapper()->delete($this->id);
	}
	
	public function insertIgnore() {
		return $this->getMapper()->insertIgnore($this);
	}	
	
	public function toArray() {
		return $this->getMapper()->toArray($this);
	}

    public function __toString()
    {
    	$dump = print_r($this->toArray(), true);    	
	    return '<pre>' . $dump . '</pre>';
    }
}