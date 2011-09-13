<?php
class App_Timer
{	
	protected $_finish_time   = null;	
	
    public function __construct($sec) {
    	$this->_finish_time = time() + (int) $sec;		
	}
	
	public function isActive() {			
	    if (time() > $this->_finish_time) 
	    	return false;	   
		return true;    
	}
	
	public function isFinish() {		
		return !$this->isActive();
	}
}