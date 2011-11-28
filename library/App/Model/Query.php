<?php
class App_Model_Query
{
   protected $currentfield = null;
   protected       $fields = array();  
   private        $enforce = array();

   // вводим новое поле
   // генерируется ошибка если предыдущее поле не заполнено
   function field($fieldname) {
      if (!$this->isEmpty() && $this->currentfield->isIncomplete()) {
         throw new Exception("Неполное поле");
      }
      $this->enforceField($fieldname);
      if ( isset($this->fields[$fieldname] ) ) {
          $this->currentfield = $this->fields[$fieldname];
      } else {
          $this->currentfield = new App_Model_Filter_Field($fieldname);
          $this->fields[$fieldname] = $this->currentfield;
      }
      return $this;
   }

   // пуст ли фильтр
   function isEmpty() {
      return empty($this->fields);
   }

   // Заданное имя поля допустимо?
   function enforceField($fieldname) {
      if (!in_array($fieldname, $this->enforce ) &&
           !empty($this->enforce)) {
         $forcelist = implode( ', ', $this->enforce );
         throw new Exception("{$fieldname} не является корректным полем ($forcelist)");
     }
   }
   
    // обязательный (не соответствующие этому элементу не соответствуют всему запросу)
    function rq($value) {
        return $this->operator("+", $value);
    }
    
    // запрещенный (соответствующие этому элементу не соответствуют всему запросу)
    function not($value) {    	
        return $this->operator("-", $value );
    }
    
    function notIn(array $range) {    	
    	return $this->operator("-", implode(' OR ', $range) );
    }

    // необязательный
    function has($value) {
        return $this->operator("",  $value );
    }
   
   private function operator($symbol, $value) {
      if ( $this->isEmpty()) {
         throw new \Exception("���� �� ����������");      }
      $this->currentfield->addValue($symbol, $value);
      return $this;
   }
 
   // Возвращаем параметры фильтра
   function getParams() {
      $result = array();
      foreach ($this->fields as $key => $field ) {
         $result = array_merge($result, $field->getParams());
      }
      return $result;
   }
   
    public function toString()
	{
		$result = array();		
		$params = $this->getParams();		
		foreach ($params as $param) {
			if (!empty($param['value'])) {
				if ('contents' != $param['name'])
			        $result[] = $param['operator'] . $param['name'] . ':(' . $param['value'] . ')';
			    else $result[] = $param['operator'] . '"' . $param['value'] . '"';
			}
		}
		return join(' ', $result);
	}   
}