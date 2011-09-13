<?php
/**
 * Модель реализующая работу с NestedSet деревьями
 */
abstract class App_Model_Zend_NestedSet_Abstract extends App_Model_Abstract
{
	
	protected $_id;                  // id узла 
	protected $_parent_id;           // id родительского узла
	protected $_name;                // имя узла	
	protected $_sort;                // индекс сортировки в пределах родительского узла
	
	protected $_left_key;            // левый ключ
	protected $_right_key;           // правый ключ
	protected $_level;               // уровень вложености
	
	/**
	 * Композиты родитель (lazy load)
	 */
	protected $_parent = null;       // родительский узел 	
	
	
	/**
	 * @return unknown_type
	 */
	public function __construct(array $options = null)
	{
		parent::__construct($options);	    
	}
	
	/**
	 * Перед непосредственным сохранением данных - parent::save() 
	 * производим нужные преобразования над деревом категорий		
	 */
	public function save()
	{
		// если это новый узел 
		if ($this->id == null)
		{ 		
			//echo '<pre>'; print_r($this->getMapper()->getDbTable()); echo '</pre>'; 		
			$this->getMapper()->getDbTable()->insertNode($this);			
		}	
		// обновление существующего узла 	
		else 
		{ 				
			// проверяем на внесение изменений влияющих 
			// на положение узла в дереве категорий
			// TODO дописать сохранение этих данных при getById			
			$old = new Model_Category();
			$old->getById($this->id);
			
			// проверяем необходимость перемещения узла(ветки)
			if ($old->parent_id != $this->parent_id  // изменена родительская катеория 
			      || $old->name != $this->name       // изменено название
			      || $old->sort != $this->sort       // изменен индекс сортировки
			) 
			{					
				// проверяем корректность задания родительской категории				
				if(!$this->checkParent())
				{
					// если родитель задан не корректно - указываем старого
					$this->parent_id = $old->parent_id;
					// обнуляем загруженый косяк и загружаем новое значение
					$this->_parent = null; 
										
				};				
							
				// подотавливаем переменные для запроса
				// ...
				$param = array();
				$param['level']     = $this->level;
				$param['left_key']  = $this->left_key;
				$param['right_key'] = $this->right_key;
				$param['level_up']  = $this->getParentNode()->level; 
				$param['right_key_near'] = $this->getRightKeyNear(); // правый ключ узла за которым мы вставляем наш узел
				$param['skew_level'] = $param['level_up'] - $param['level'] +1;
				$param['skew_tree']  = $param['right_key'] - $param['left_key'] +1;	
				
				// вначале сохраняем пользовательские данные				
				$result = parent::save();
				// и выполняем преобразования над деревом
				if ($param['right_key_near'] > $this->right_key)
				{
					// перемещение вниз по дереву	
					$param['skew_edit'] = $param['right_key_near'] - $param['left_key'] + 1 - $param['skew_tree'];		
					$this->getMapper()->getDbTable()->moveDown($this, $param);
				}
				else 
				{
					// перемещение вверх по дереву	
					$param['skew_edit']  = $param['right_key_near'] - $param['left_key'] + 1;					
					$this->getMapper()->getDbTable()->moveUp($this, $param);
				}	
				return $result;			
			}					
		}
		return parent::save();		
	}

	/**
	 * задача функции получить правый ключ узла за которым вставляем
	 * перемещаемый узел, ветку (с учетом сортировки по sort и name).
	 */
	private function getRightKeyNear()
	{		
		return $this->getMapper()->getDbTable()->getRightKeyNear($this);		
	}
	
	 /**
	  * Перед непосредственным удалением данных - parent::delete() 
	  * производим нужные преобразования над деревом категорий	
	  */
	 public function delete()	 
	 { 
	 	 $this->getMapper()->getDbTable()->deleteNode($this);
	     //return parent::delete();	 	
	 }
	/**
	 * Функция возвращает родителя для текущего узла
	 * если родитель отсутствует - возвращает объект с параметрами root 
	 */
	public function getParentNode()
	{
		if (null === $this->_parent)
        {     
        	// новый объект автоматически указывает на root
        	$parent = new Model_Category();   
	        if ($this->parent_id != null) 
	        {    	             
	             $parent->getById($this->parent_id);	             	                         
	        }	      
	        else // родитель - root
	        {
	             $parent->left_key  = 0;
			     $parent->right_key = $this->getMapper()->getDbTable()->getMaxRightKey() + 1;
			     $parent->level     = 0;	
	        }	         
	        $this->_parent = $parent;       
        }
        return $this->_parent;
	} 
	
    public function getParents()
	{				
		return $this->getMapper()->getDbTable()->getParents($this);
	}
	
	
    /**
	 * Проверка заданной родительской категории на корректность
	 */
	private function checkParent()
	{		
		// указанный parent_id не должен являтся потомком текущего узла
        $children = $this->getChildren();
        foreach ($children as $node)
        {
        	if ($this->getParentNode()->id == $node['id']) return false;
        }
        return true;		
	}
	/**
	 * Извлечение дерева разделов (категорий)
	 */
	public function getTree()
	{
		return $this->getMapper()->getDbTable()->getTree();
	}
	
    /**
	 * Проверка дерева разделов (категорий) на целостность
	 */
	public function checkTree()
	{
		return $this->getMapper()->getDbTable()->checkTree();
	}
	
    public function getChildren()
	{				
		return $this->getMapper()->getDbTable()->getChildren($this);
	}
	
	
    public function getElements()
	{				
		return $this->getMapper()->getDbTable()->getChildren($this);
	}
	   
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
     * Индекс сортитровки в пределах родительского узла
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
    public function setLeft_key($value)
    {
        $this->_left_key = (int)$value;    
        return $this;
    }

    public function getLeft_key()
    {
        return $this->_left_key;
    }
    
    /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setRight_key($value)
    {
        $this->_right_key = (int)$value;    
        return $this;
    }

    public function getRight_key()
    {
        return $this->_right_key;
    }
    
    /**
     * @param unknown_type $value
     * @return unknown_type
     */
    public function setLevel($value)
    {
        $this->_level = (int)$value;    
        return $this;
    }

    public function getLevel()
    {
        return $this->_level;
    }
	
	
	
}