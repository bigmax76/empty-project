<?php
class App_Paginator extends Zend_Paginator
{
	static protected $current_page = 1;
	
	static public function setPage($page)
	{
		self::$current_page = (int)$page;
	}
	
	public function __construct($adapter)
	{
	    parent::__construct($adapter);
	 	$this->setCurrentPageNumber(self::$current_page);
    }
}