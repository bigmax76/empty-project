<?php
$front = Zend_Controller_Front::getInstance();
$router = $front->getRouter();

// ---------------------------------------------
$router->addRoute('example',         new Zend_Controller_Router_Route('/example/:param1/:param2', array('module'=>'default','controller'=>'index','action'=>'list')));