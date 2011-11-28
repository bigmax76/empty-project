<?php
class App_Model_Filter_Field 
{
   protected $_name;
   protected $_operator;
   protected $_params     = array();
   protected $_incomplete = false;

   // устанавливает имя поля
   function __construct($name) {
      $this->_name = $name;
   }

  // добавляем оператор и значение 
  function addValue($operator, $value) {
     $this->_params[] = array(
         'name'     => $this->_name,
         'operator' => $operator,
         'value'    => $value
     );
   }
   
   function getParams()    {return $this->_params;}
   function isIncomplete() {return empty( $this->_params);}
}