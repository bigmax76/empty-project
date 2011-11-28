<?php 
class App_Model_Registry
{
	protected static $_className;
	protected static $_classService;
	protected static $_objects = array();
	protected static $_data    = array();
    
	/**
	 * инициализирует реестр переданным диапазоном id
	 */
	public static function getByRange(array $range)
	{
    	$service = static::$_classService;
    	$rows = $service::getByRange($range);
    	// TODO возможно имеет смысл делать merge чтобы не затирать ранее добавленные данные
    	static::$_data[get_called_class()] = App_Common_Array::toAssoc($rows, 'id');
    	return $rows;
    }
	
    public static function prepare($elements, $key) 
    {    	
    	if ($elements instanceof App_Model_Collection)
    	    $elements = $elements->getData();
    	
    	$range = App_Common_Array::getCol($elements, $key);
    	return self::getByRange($range);
    }
    
    public static function getById($id)
    {		
    	$model = get_called_class();
		// если массив уже был преобразован в объект - возвращаем его 
		if (isset( self::$_objects[$model][$id])) {
			return self::$_objects[$model][$id];
		}
		
		// возможно есть сырые данные
		if (isset( self::$_data[$model][$id])) {
			self::$_objects[$model][$id] = new static::$_className(self::$_data[$model][$id]);
			return self::$_objects[$model][$id];
		}
		
		// иначе просто создаем нужный объект
		$obj = new static::$_className();
		$obj->getById($id);
		self::$_objects[$model][$id] = $obj;
		return self::$_objects[$model][$id];
    }

}