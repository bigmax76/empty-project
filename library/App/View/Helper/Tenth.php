<?php
/**
 * Уменьшение величины в 10 раз
 */
// TODO Добавить число знаков после запятой
class App_View_Helper_Tenth extends Zend_View_Helper_Abstract {
	public function tenth($value) {    	
    	$output = $value/10;
    	return $output;
    }
}