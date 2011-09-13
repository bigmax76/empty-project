<?php

/**
 * Этот фильтр переопредяляет стандартный фильтр Zend_Filter_StringToLower
 * указывая для него нужную для проекта кодировку
 * Единственным недостатком является то, что необходимо для каждого элемента указывать
 * ->addPrefixPath('App_Form_Filter', 'App/Form/Filter' , 'filter')							
 *  
 * @author таргет
 *
 */
class App_Form_Filter_StringToLower extends Zend_Filter_StringToLower
{
	 protected $_encoding = 'utf-8';	 
}