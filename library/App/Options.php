<?php 
class App_Options
{  
    public static function setConstructorOptions($object, array $options)      {          if (!is_object($object)) {              return;          }          foreach ($options as $key => $value) {              $method = 'set' . self::_normalizeKey($key);                          if (method_exists($object, $method)) {                  $object->$method($value);              }          }    }      public static function setOptions($object, $options)    {          if ($options instanceof Zend_Config) {              $options = $options->toArray();          }          if (is_array($options)) {              self::setConstructorOptions($object, $options);          }      }  
    
    public static function merge(array $array1, $array2 = null)
    {
        if (is_array($array2)) {
            foreach ($array2 as $key => $val) {
                if (is_array($array2[$key])) {
                    $array1[$key] = (array_key_exists($key, $array1) && is_array($array1[$key]))
                                  ? self::merge($array1[$key], $array2[$key])
                                  : $array2[$key];
                } else {
                    $array1[$key] = $val;
                }
            }
        }
        return $array1;
    }     protected static function _normalizeKey($key)      {          //$option = str_replace('_', ' ', strtolower($key));          $option = ucwords(strtolower($key));          return $option;      }          /**     * Может использоватся например для нормализации ключей для Zend_Cache     */    public static function normalize($key)    {    	$key = str_replace(array('-','/','.'), '_', strtolower($key));    	        $key = strtolower($key);        return $key;    }}  