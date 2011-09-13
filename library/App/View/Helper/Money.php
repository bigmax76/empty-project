<?php
/**
 * Хелпер предназначен для автоматического перевода копеек в гривны
 *  * @author таргет
 *
 */
class App_View_Helper_Money extends Zend_View_Helper_Abstract
{
	public function money($sum)
    {
    	//echo $sum;
    	$output = $sum/100;
    	return $output;
    }
}