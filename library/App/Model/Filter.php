<?php
class App_Model_Filter
{
   protected $currentfield = null;
   protected       $fields = array();
   protected        $sorts = array();
   private        $enforce = array();

   // ����������� identity object ����� ����������� 
   // ��� ���������� ��� � ������ ����
   function __construct($field=null, array $enforce=null ) {
      if (!is_null($enforce)) {
         $this->enforce = $enforce;
      }
      if (!is_null($field)) {
         $this->field($field);
      }
   }

   // Имена полей на которые наложено это ограничение
   function getObjectFields() {
      return $this->enforce;
   }

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

   public function sort($field, $direction = 'ASC')
   {
   	    $this->sorts[] = strtolower($field) . ' ' . strtoupper($direction); 
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
   
   // равно
   function eq($value) {
   	   if (is_array($value))
           return $this->operator("in (?)", $value);	   
       return $this->operator("= ?", $value);
   }

   // меньше чем
   function lt( $value ) {
      return $this->operator( "< ?", $value );
   }

   // больше чем
   function gt( $value ) {
       return $this->operator( "> ?", $value );
   }
   
   // не
   function not($value) {
   	   // TODO При передаче пустого массива возникает sql error
   	   if (is_array($value))
   	       return $this->operator("not in (?)", $value);
       return $this->operator( "!= ?", $value );
   }
   
   function like($value) {
       return $this->operator( "like ?", $value );	  
   }
    
//   // в диапазоне
//   function in(array $value) {
//   	    return $this->operator("in (?)", $value);
//   }   
   
   
   // ��������� ������ ��� ������� operator.
   // �������� ������� ���� � ��������� �������� ��������� 
   // � ���������� ����� � ����
   private function operator( $symbol, $value ) {
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
   
   // Возвращаем параметры сортировки
   function getSorts() {      
      return $this->sorts;
   }
   
}