<?php
/**
 *   Sql необходимый для работы с Nested Set деревьями
 */
abstract class App_Model_Zend_NestedSet_DbTable extends Zend_Db_Table_Abstract 
{
	// нужно подумать о добавлении индексов к этой таблице.
	
	/**
     * Извлечение всего дерева категорий
     */
    public function getTree()
    {    
  	    // оригинальный sql из статьи  	
  	    $sql = 'SELECT id, name, level FROM $this->name ORDER BY left_key'; 
  	   
        $select = $this->getAdapter()->select();
        $select ->from($this->_name)
        		->order('left_key ASC');  // порядок сортировки       
        // Выполнение запроса
        $stmt = $this->getAdapter()->query($select);
        // Получение данных в виде ассоциативного массива
        $result = $stmt->fetchAll();
  	    
        return $result;
    }
    
    /**
     * Извлечение дерева потомков (включая текущую категорию)
     */
    public function getChildren($category)
    {        	
        // оригинальный sql из статьи  	
  	    $sql = 'SELECT id,name,level FROM my_tree WHERE left_key >= [left_key] AND right_key <= [right_key] ORDER BY left_key'; 
  	   
        $select = $this->getAdapter()->select();
        $select ->from($this->_name)
                ->where('left_key  > ?' ,$category->left_key)
                ->where('right_key < ?' ,$category->right_key)
        		->order('left_key ASC');  // порядок сортировки     
       
        // Выполнение запроса
        $stmt = $this->getAdapter()->query($select);
        // Получение данных в виде ассоциативного массива
        $result = $stmt->fetchAll();
  	    
        return $result;   	
    }
    
    /**
     * Извлечение дерева родителей (включая текущую категорию)
     */
    public function getParents($category)
    {    
        // оригинальный sql из статьи  	
  	    $sql = 'SELECT id,name,level FROM my_tree WHERE left_key >= [left_key] AND right_key <= [right_key] ORDER BY left_key'; 
  	   
        $select = $this->getAdapter()->select();
        $select ->from($this->_name)
                ->where('left_key  <= ?' ,$category->left_key)
                ->where('right_key >= ?' ,$category->right_key)
        		->order('left_key ASC');  // порядок сортировки       
        // Выполнение запроса
        $stmt = $this->getAdapter()->query($select);
        // Получение данных в виде ассоциативного массива
        $result = $stmt->fetchAll();
  	    
        return $result;   	
    }
    
    /**
     * Преобразование параметров дерева необходимых для
     * добавления нового узла
     * Исходными данными для работы является parent_id
     * то есть указав parent_id и вызвав метод $category->save
     *  
     */
    public function insertNode($node)
    {    
    	$parent = $this->getParentNode($node->parent_id);

    	$right_key = $parent->right_key;
        //$right_key = $node->getParentNode()->right_key;       
        			        
  	    //$db = Zend_Registry::get('db');	
  	    //$db = App_Resource::getResource('db');
  	    //$db = $this->getDbAdapter();
  	    
        $sql = 'UPDATE ' . $this->_name . ' ' .
        		 'SET right_key = right_key + 2, ' .
                     'left_key = CASE WHEN (left_key > ' . $right_key . ') ' .
                     				 'THEN left_key + 2 ' .
        							 'ELSE left_key ' .
                                'END ' .
                 'WHERE right_key >= ' . $right_key ; 
        //echo '<pre>'; print_r($sql); echo '</pre>';
        $stmt = $this->getAdapter()->query($sql);
        //$stmt = $db->query($sql);             
    	
        // устанавливаем новые параметры для вставляемого узла
        $node->left_key  = $right_key;
        $node->right_key = $right_key + 1;
        $node->level     = $parent->level + 1;
        //$node->level = $node->getParentNode()->level + 1;      	
    }
    
    protected function getParentNode($parent_id)
    {    	
    	// отдаем правый ключ родителя    	
    	if ($parent_id != null || $parent_id != 0) {
	    	$rowset = $this->find($parent_id);  
	    	return $rowset->current(); 
    	}
    	// или характеристики корня
    	return $this->getRoot();
    }
    
    /**
     * Возвращает характеристики root (корня дерева)
     */
    protected function getRoot()
    {
    	$sql = 'Select MAX(right_key) AS right_key, 0 AS left_key, 0 AS level From  ' . $this->_name;    	
    	$db  = $this->getAdapter();
    	
    	$db->setFetchMode(Zend_Db::FETCH_OBJ); // извлекаем данные в виде объектов
        $stmt   = $db->query($sql);
        $result = $stmt->fetchAll();
        $result = $result['0'];
        
        if ($result->right_key == null)
        	$result->right_key = 0;        
        $result->right_key++;
       
        return $result;  	
    }
    
    /**
     * Преобразование параметров дерева необходимых для удаления узла(ветки)     
     */
    public function deleteNode($node)
    {    
    	if (null == $node->id) return; // нельзя удалить то, чего нет
    	//$db = Zend_Registry::get('db');
    	//$db = App_Resource::getResource('db');
    	$db = $this->getAdapter();	
    	$left_key  = $node->left_key;
        $right_key = $node->right_key;
        $shift = $right_key - $left_key +1;
        			        
  	    // удаляем узел (ветку)
        $sql = 'DELETE FROM ' . $this->_name . ' ' .        		 
                 'WHERE left_key  >= ' . $left_key . ' AND ' .
                       'right_key <= ' . $right_key ;  
        //die ($sql);       
        $stmt = $db->query($sql);             
    	        
        // обновление родительской ветки и  последующих узлов
        $sql = 'UPDATE ' . $this->_name . ' ' .
				   'SET left_key = CASE WHEN (left_key > ' . $left_key . ') ' .
				                       'THEN left_key - ' . $shift . ' ' .
				                       'ELSE left_key ' .
				                   'END, ' .
				       'right_key = right_key - ' . $shift . ' ' .
				   'WHERE right_key > ' . $right_key ;        
        //die ($sql); 
        $stmt = $db->query($sql);        
        
    }
       
  
    /**
     * Получение максимального правого ключа дерева категорий
     */
    public function getMaxRightKey()
    {    
  	    //$db = Zend_Registry::get('db');
  	    //$db = App_Resource::getResource('db');
  	    $db = $this->getAdapter();	
        $sql = 'SELECT MAX(right_key) FROM ' . $this->_name;     	
        $stmt = $db->query($sql);               
    	$result = $stmt->fetch();  // Zend_Db::FETCH_NUM    	
    	return $result['MAX(right_key)'];    	
    }
    
    /**
     * Пололожение узла в пределах категории определяется
     * параметрами sort и name 
     * Эта функция находит правый ключ узла который бeдет 
     * первым "левым" соседом от заданного $options    
     */
    public function getRightKeyNear($node)
    {    
    	//echo '<pre>'; print_r($node);echo'</pre>'; 
  	    //$db = Zend_Registry::get('db');	
  	    //$db = App_Resource::getResource('db'); 
  	    $db = $this->getAdapter();        
        $sql = 'SELECT * FROM ' . $this->_name . ' ' .
        		        'WHERE parent_id = "' . $node->parent_id   . '" AND ' .        
        				  '( ' . 'sort < "' . $node->sort . '" '  . ' OR '. 
        		                 'name < "' . $node->name . '" )' . ' AND ' . 
                                  'id != "' . $node->id . '" ' .
                        'ORDER BY sort DESC, name DESC';
        //echo $sql; 
        $stmt = $db->query($sql);               
    	$result = $stmt->fetch();  // получаем первую запись
    	//echo '<pre>'; print_r($result);echo'</pre>';
    	// die($sql);
    	if ($result != null )
    	{
    		//die('Все плохо');
    		return $result['right_key'];
    	}  
    	else 
    	{   // если левых соседей внутри категории нет - указываем
    		// левым соседом parent_id
    		return $node->getParentNode()->left_key;
    	}
    	;
    }
    
    /**
     * Перемещает узел(ветку) выше по списку в пределах родительской категории
     */
    public function moveDown($node , $param)
    {
    	//$db = Zend_Registry::get('db');
    	//$db = App_Resource::getResource('db');
    	$db = $this->getAdapter();
    	 // удаляем узел (ветку)
    	//echo '<pre>'; print_r($param);echo'</pre>';
    	//die('moveDown');
        $sql = 'UPDATE ' . $this->_name . ' ' .
				   'SET ' .
				       'left_key = CASE WHEN right_key <= ' . $param['right_key'] . ' ' .
				                       'THEN left_key + ' . $param['skew_edit'] . ' ' .
				                       'ELSE CASE WHEN left_key > ' . $param['right_key'] . ' ' .
				                                 'THEN left_key - ' . $param['skew_tree'] . ' ' .
				                                 'ELSE left_key ' .
				                            'END ' .
				                  'END, ' .
				       'level = CASE WHEN right_key <= ' . $param['right_key'] . ' ' .
				                    'THEN level + ' . $param['skew_level'] . ' ' .
				                    'ELSE level ' .
				               'END, ' .
				       'right_key = CASE WHEN right_key <= ' . $param['right_key'] . ' ' .
				                        'THEN right_key + ' . $param['skew_edit'] . ' ' .
				                        'ELSE CASE WHEN right_key <= ' . $param['right_key_near'] . ' ' .
				                                  'THEN right_key - ' . $param['skew_tree'] . ' ' .
				                                  'ELSE right_key ' .
				                             'END ' .
				                   'END ' .
				   'WHERE ' .
				       'right_key > ' . $param['left_key'] . ' AND ' . 
				       'left_key <= ' . $param['right_key_near'];	
        //die ($sql);       
		$stmt = $db->query($sql); 
    }
    
    /**
     * Перемещает узел(ветку) ниже по списку в пределах родительской категории
     */
    public function moveUp($node , $param)
    {
    	//$db = Zend_Registry::get('db');
    	//$db = App_Resource::getResource('db');
    	$db = $this->getAdapter();
    	// удаляем узел (ветку)
    	//echo '<pre>'; print_r($param);echo'</pre>';
    	//die('moveUp');
        $sql = 'UPDATE ' . $this->_name . ' ' .
				   'SET ' .
				       'right_key = CASE WHEN left_key >= ' . $param['left_key']  . ' ' . 
				                        'THEN right_key + ' . $param['skew_edit'] . ' ' .
				                        'ELSE CASE WHEN right_key < ' . $param['left_key']  . ' ' .
				                                  'THEN right_key + ' . $param['skew_tree'] . ' ' .
				                                  'ELSE right_key ' .
				                             'END ' . 
				                   'END, ' .
				       'level = CASE WHEN left_key >= ' . $param['left_key'] . ' ' .
				                    'THEN level + ' . $param['skew_level'] . ' ' .
				                    'ELSE level ' .
				               'END, ' .
				       'left_key = CASE WHEN left_key >= ' . $param['left_key'] . ' ' .
				                       'THEN left_key + ' . $param['skew_edit'] . ' ' .
				                       'ELSE CASE WHEN left_key > ' . $param['right_key_near'] . ' ' .
				                                 'THEN left_key + ' . $param['skew_tree'] . ' ' .
				                                 'ELSE left_key ' .
				                            'END ' .
				                  'END ' .
				   'WHERE ' .
				       'right_key > ' . $param['right_key_near'] . ' AND ' .
				       'left_key < ' . $param['right_key'];
		//die ($sql);       
		$stmt = $db->query($sql);            
    }
    
    /**
     * Проверка дерева разделов на целостность
     * Взято на  http://doc.prototypes.ru/database/nestedsets/theory/rules/
     * Проверка основана на следующих правилах
     * 1. Левый ключ ВСЕГДА меньше правого (1);
     * 2. Наименьший левый ключ ВСЕГДА равен 1 (2);
     * 3. Наибольший правый ключ ВСЕГДА равен двойному числу узлов.(2) Отсюда же правило, что разрывов последовательности ключей быть не может;
     * 4. Разница между правым и левым ключом ВСЕГДА нечетное число (3);
     * 5. Если уровень узла нечетное число то тогда левый ключ ВСЕГДА нечетное число, то же самое и для четных чисел (4);
     * 6. Ключи ВСЕГДА уникальны, вне зависимости от того правый он или левый (5). Но это правило, увы, неполучится реализовать на уровне уникального индекса, т.к. в процессе перестроения дерева, в пределах транзакции данное правило не работает;
     */
    public function checkTree()
    {
    	if (!$this->rule1()) throw new Exception('Ошибка формирования дерева категорий: left_key не может быть больше right_key');
    	if (!$this->rule2()) throw new Exception('Ошибка формирования дерева категорий: Наименьший left_key не равен 1 или наибольший right_key не равен двойному числу узлов');
    	if (!$this->rule3()) throw new Exception('Ошибка формирования дерева категорий: Разница между правым и левым ключом ВСЕГДА нечетное число');
    	if (!$this->rule4()) throw new Exception('Ошибка формирования дерева категорий №4');
    	if (!$this->rule5()) throw new Exception('Ошибка формирования дерева категорий: Не уникальные значения ключей left_key и/или  right_key');
    	return true;  	   
    }
    
    /**
     * Проверка правила №1 (левый ключ ВСЕГДА меньше правого)
     */
    private function rule1()
    {    		
    	// $sql = 'SELECT id FROM my_tree WHERE left_key >= right_key'; 
    	
    	$select = $this->getAdapter()->select();
        $select ->from($this->_name)
        		->where('left_key >= right_key');        		       
        // Выполнение запроса
        $stmt = $this->getAdapter()->query($select);
        // Получение данных в виде ассоциативного массива
        $result = $stmt->fetchAll();
        //echo '<pre>'; print_r($result);echo'</pre>';        
        if (count($result) != 0) return false;  	    
        return true;
    }
    
    /**
     * Проверка правила №2 и №3
     * Наименьший левый ключ ВСЕГДА равен 1 (2);
     * Наибольший правый ключ ВСЕГДА равен двойному числу узлов.(2) Отсюда же правило, что разрывов последовательности ключей быть не может;
     * 
     */
    private function rule2()
    {    	
    	//$db = Zend_Registry::get('db');	
    	//$db = App_Resource::getResource('db');
    	$db = $this->getAdapter();
        $sql = 'SELECT COUNT(id), MIN(left_key), MAX(right_key) FROM ' . $this->_name;     	
        $stmt = $db->query($sql);
               
    	$result = $stmt->fetch();  // Zend_Db::FETCH_NUM
    	if ($result['COUNT(id)'] == 0) return true; // пустое дерево можем считать корректным
    	if ($result['COUNT(id)']*2   != $result['MAX(right_key)']) return false;
    	if ($result['MIN(left_key)'] != 1) return false;
    	    
        return true;
    }
    
    /**
     * Проверка правила №4
     * Разница между правым и левым ключом ВСЕГДА нечетное число (3);
     */
    private function rule3()
    {    	
    	//$db = Zend_Registry::get('db');
    	//$db = App_Resource::getResource('db');
    	$db = $this->getAdapter();	
    	$sql = 'SELECT * FROM ' . $this->_name . ' WHERE MOD( right_key - left_key  , 2 ) = 0';
    	$stmt = $db->query($sql);
              
    	$result = $stmt->fetchAll();  
    	if (count($result) != 0) return false;  	    
        return true;
    }
    
    /**
     * Проверка правила №5
     * Если уровень узла нечетное число то тогда левый ключ 
     * ВСЕГДА нечетное число, 
     * то же самое и для четных чисел (4);
     */
    private function rule4()
    {    	
    	//$db = Zend_Registry::get('db');	
    	//$db = App_Resource::getResource('db');
    	$db = $this->getAdapter();
    	$sql = 'SELECT * FROM ' . $this->_name . ' WHERE MOD(( left_key - level + 2 ) , 2 ) = 1';
    	$stmt = $db->query($sql);
              
    	$result = $stmt->fetchAll();  
    	if (count($result) != 0) return false;  	    
        return true;
    }
    
    /**
     * Проверка правила №6
     * Ключи ВСЕГДА уникальны, вне зависимости от того правый он или левый (5). 
     * Но это правило, увы, неполучится реализовать на уровне 
     * уникального индекса, т.к. в процессе перестроения дерева, 
     * в пределах транзакции данное правило не работает;
     */
    private function rule5()
    {    	
    	//$db = Zend_Registry::get('db');	
    	//$db = App_Resource::getResource('db');
    	$db = $this->getAdapter();
    	$sql = 'SELECT * FROM ' . $this->_name;
    	$stmt = $db->query($sql);

    	$result = array();
    	$cnt = 0 ;
        while ($row = $stmt->fetch()) {
		    $result[$row['left_key']]  = '';
		    $result[$row['right_key']] = '';
		    $cnt++;
		}    	
    	if (count($result) != $cnt*2) return false;  	    
        return true;
    }  
        
}