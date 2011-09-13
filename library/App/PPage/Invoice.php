<?php
class App_PPage_Invoice 
{
	const NON_CASH_PAYMENT = 0;
	const CASH_PAYMENT     = 1;
	const WM_PAYMENT       = 2;
	
    public static function factory($payment_type, $param = array())
    {
        /*
         * Убеждаемся, что передан массив параметров платежа
         */
        if (!is_array($param)) {            
            throw new Exception('Параметры платежа должны быть переданны в массиве');
        }

        /*
         * Убеждаемся, что указан способ платежа
         */
        if (!is_string($payment_type) || empty($payment_type)) {
            /**
             * @see Zend_Db_Exception
             */            
            throw new Exception('Способ платежа должен быть указан строкой');
        }

        /*
         * Формируем полное имя класса нужного типа платежа 
         */
        $namespace = 'App_PPage_Invoice';
        if (isset($param['namespace'])) {
              if ($param['namespace'] != '') {
                  $adapterNamespace = $param['namespace'];
            }
            unset($param['$namespace']);
        }
        //$className = strtolower($namespace . '_' . $payment_type);
        //$className = str_replace(' ', '_', ucwords(str_replace('_', ' ', $className)));
		$className = $namespace . '_' . ucwords($payment_type);
        //$className = str_replace(' ', '_', ucwords(str_replace('_', ' ', $className)));
        
        
        /*
         * Загружаем класс с нужным типом платежа. Это вызовет исключение 
         * если указанный класс не может быть загружен         
         */
        if (!class_exists($className)) {
            //require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($className);
        }

        /*
         * Cоздаем экземпляр счета нужного типа и 
         * передаем полученные параметры его конструктору
         */
        $invoice = new $className($param);

        /*
         * Убеждаемся, что созданый объект является потомком абстрактного типа платежа
         */
        if (! $invoice instanceof App_PPage_Invoice_Abstract) {
            throw new Exception("Класс '$invoice' не является наследником App_PPage_Invoice_Abstract");
        }

        return $invoice;
    }
	
    /**
     * Костыль
     * Так как мы храним в одной таблице несколько видов объекта - 
     * мы не можем знать наперед какой объект хранится по переданному id. 
     * Поэтому извлекаем как NonCash и если не попали - нужный 
     * Это производит дополнительный запрос к бд но пока это не критично
     */
    public static function getById($id)
    {
    	$invoice = new App_PPage_Invoice_NonCash();
    	$invoice->getById($id);    	
    	switch ($invoice->payment_type) {
		    case App_PPage_Invoice::NON_CASH_PAYMENT :
		    	return $invoice;
		        		
		    case App_PPage_Invoice::CASH_PAYMENT:
		    	$invoice = new App_PPage_Invoice_Cash();
		    	return $invoice->getById($id);
		        
		    case App_PPage_Invoice::WM_PAYMENT:
		    	$invoice = new App_PPage_Invoice_Wm();
		    	return $invoice->getById($id);
		        
		    default: throw new Exception('Не опознан способ платежа');
		        break;
		}    	
    }

}