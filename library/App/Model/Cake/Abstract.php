<?php
/**
 * Абстрактный класс реализующий основные функции 
 * связаные с персистентностью своих потомков
 * @author таргет
 *
 */
abstract class App_Model_CakeAbstract
{   
	/**
	 * Имя маппера для объекта по умолчанию. Устанавливается для в классе потомке
	 * (например  'Model_ObjNameDbMapper') 
	 * @var unknown_type
	 */
	protected $_mapperName = Null;
	
	protected $_mapper;
	 
    /**
	 * Конструктор объекта модели. Заполняет поля объекта данными из переданного массива.
	 * (CRUD - Create)
	 * @param array $data
	 * @return unknown_type
	 */
	public function __construct(array $options = null)
    {    	
        if (is_array($options)) {
            $this->setOptions($options);
        }
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
            throw new Exception('Invalid shop property: ' . $name);
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
    public function setOptions(array $data)
    {
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
        $this->_mapper = $mapper;
        return $this;
    }

    /**
     * Функция возвращает текущий маппер для объекта 
     * если маппер не задан функцией setMapper() - она вернет указанный в _mapperName  
     * @param unknown_type $mapper
     * @return unknown
     */
    public function getMapper()
    {
        if (null === $this->_mapper)
        {       	
        	$mapper = $this->_mapperName;   
	        if (is_string($mapper)) {
	            $mapper = new $mapper(); 
	        }
	        if (!$mapper instanceof App_Model_Abstract_CakeMapper) {
	            throw new Exception('Ошибка установки маппера: ' . $mapper);
	        }
            $this->setMapper(new $mapper);
        }
        return $this->_mapper;
    }
    
    /**
     * Возвращает объект по его id
     * @param unknown_type $id
     * @return unknown
     */
    public function getById($id)
    {
        $this->getMapper()->getById($id, $this);
        return $this;
    }
    
    /**
     * Возвращает объект по его коду
     * @param unknown_type $code
     * @return unknown
     */
    public function getByCode($code)
    {
    	if (empty($code)) return null;    	
    	return $this->getMapper()->getByCode($code, $this);    	    
    }    
    
    public function getByField($field_name, $value)
    {
    	if (empty($field_name)) return null; 
    	return $this->getMapper()->getByField($field_name, $value, $this);
    }
    
    /**
     * сохраняет объект и возвращает id новой или измененной записи
     * @return unknown_type
     */
    public function save()
    {	
       if (null == $this->getId()){
       	   $this->preInsert();
       	   $isInsert = true;
       }else{
       	   $isInsert = false;
       	   $this->preUpdate();
       }
       
       $result = $this->getMapper()->save($this);   
       
       if ($isInsert){
       	   $this->setId($result); 
       	   $this->postInsert();
       }            
       else 
           $this->postUpdate();

       return $result;
    }
    
    public function delete()
	{
		$this->preDelete();
		$result = $this->getMapper()->delete($this->getId());
		$this->postDelete();
		return $result;
	}
	
	public function toArray()
	{
		return $this->getMapper()->toArray($this);
	}
	
	////////////////////////////////
	// реализуем позже
	////////////////////////////////
	protected function preInsert()
    {
    	return true;
    }
    protected function postInsert()
    {
        return true;
    }
    protected function preUpdate()
    {
        return true;
    }
    protected function postUpdate()
    {
        return true;
    }

    protected function preDelete()
    {
        return true;
     }
    protected function postDelete()
    {
        return true;
    }
    protected function postLoad()
    {
        return true;
    }    
}