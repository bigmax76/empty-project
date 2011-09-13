<?php
/**
 *  Если передано не пустое значение - выводит его в теге h1
 */
class App_View_Helper_Title extends Zend_View_Helper_Url
{
    public function title($title) {               
        if (!empty($title)) 
        	return '<h1>'. $title .  '</h1>';           
    }
}