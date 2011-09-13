<?php
/**
 *  Печатает число если это не ноль
 */
class App_View_Helper_NoZiro extends Zend_View_Helper_Url
{
    public function noZiro($cnt)
    {
       if ('0' === $cnt)
       	return;       	
       return $cnt;
    }
}