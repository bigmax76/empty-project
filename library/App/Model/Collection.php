<?php
abstract class App_Model_Collection implements Iterator, Countable, ArrayAccess
{
	protected $_className = null;   // имя класса экземпляра коллекции
		
	protected $total;               // кол-во элементов массива данных
	protected $pointer = 0;         // указатель на текущий элемент
	protected $data    = array();   // исходный массив данных		
	protected $object  = null;      // массив готовых объектов
	
	function __construct($data = null)
	{
		if (null === $this->_className) 
			throw new Exception('Не задан класс экземпляра коллекции');	
					
		if ($data instanceof Zend_Paginator) {	
			// TODO перенести создание объектов в getRow()		
			$rows = $data->getCurrentItems()->toArray();			
			$id = 0;
			foreach ($rows as $row) {
				$this->object[$id] = new $this->_className($row);
				$id++;				
			}	
			$this->data  = $data;
			$this->total = $data->getCurrentItemCount();			
			return;
		}		
		
	    if ($data instanceof Zend_Db_Table_Rowset) {
			$data = $data->toArray();	
			$this->data  = $data;
			$this->total = count($data);			
			return;
	    }		
					
		if(is_array($data)) {
			$this->data = $data;
			$this->total = count($data);			
			return;
		}
				
        throw new Exception('Не поддерживаемый тип данных!');	
	}

	private function getRow($id)
	{					
		// если id не входит в допустимый диапазон - null 
		if ($id >= $this->total || $id < 0 ) 
			return null;		 
		
		// если массив уже был преобразован в объект - возвращаем его 
		if (isset($this->object[$id])) {
			return $this->object[$id];
		}
		
		// иначе создаем объект 
		if (isset($this->data[$id])) {
			$this->object[$id] = new $this->_className($this->data[$id]);
			return $this->object[$id];
		}
	}
	
	public function rewind() {
		$this->pointer = 0;
	}
	
	public function current() {
		return $this->getRow($this->pointer);
	}
	
	public function key() {
		return $this->pointer;
	}
	
	public function next()	{			
		$this->pointer++;
	}
	
	public function valid(){		
		return (!is_null($this->current()));
	}	
	
	public function count() {
		return $this->total;
	}
	
	public function getTotalItemCount(){
		if ($this->data instanceof Zend_Paginator)
			return $this->data->getTotalItemCount();
		return $this->total;
	}
	
	public function getPaginator()
	{
		if ($this->data instanceof Zend_Paginator)
			return $this->data; 
		return App_Paginator::factory($this->data);
		// не уверен но помоему лучше  
		// return App_Paginator::factory($this);
	}
	
	
	// Методы для соответствия Array Access
	// TODO нужно переписать и убедится что работает
	
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        } 
    }
    
    public function offsetExists($offset) {
    	$obj = $this->getRow($offset);
        return isset($obj);
    }
    
    public function offsetUnset($offset) {
    	$obj = $this->getRow($offset);
        unset($obj);
    }
    
    public function offsetGet($offset) {
    	return $this->getRow($offset);
    }

    
}