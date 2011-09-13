<?php 
class App_Service_Facebook_Exception extends Exception
{
    protected $result;

    public function __construct($result) 
    {
	    $this->result = $result;	
	    $code = isset($result['error_code']) ? $result['error_code'] : 0;
	
	    if (isset($result['error_description'])) {
	        // OAuth 2.0 Draft 10 style
	        $msg = $result['error_description'];
	    } else if (isset($result['error']) && is_array($result['error'])) {
	        // OAuth 2.0 Draft 00 style
	        $msg = $result['error']['message'];
	    } else if (isset($result['error_msg'])) {
	        // Rest server style
	        $msg = $result['error_msg'];
	    } else {
	        $msg = 'Unknown Error. Check getResult()';
	    }
	
	    parent::__construct($msg, $code);
    }
    
    public function getResult() {
        return $this->result;
    }

    /**
     * Returns the associated type for the error. This will default to
     * 'Exception' when a type is not available.
     */
    public function getType() 
    {
	    if (isset($this->result['error'])) 
	    {
	        $error = $this->result['error'];
	        if (is_string($error)) {
	            // OAuth 2.0 Draft 10 style
	            return $error;
	        } else if (is_array($error)) {
		        // OAuth 2.0 Draft 00 style
		        if (isset($error['type'])) {
		            return $error['type'];
	            }
	        }
	    }
	    return 'Exception';
    }

    /**
     * To make debugging easier.
     */
    public function __toString() 
    {
	    $str = $this->getType() . ': ';
	    if ($this->code != 0) {
	      $str .= $this->code . ': ';
	    }
	    return $str . $this->message;
    }
}