<?php
require_once 'Facebook/Facebook.php';
class App_Service_Facebook
{
    protected $_app_id;
    protected $_app_secret;
    protected $_app_perm;  
    protected $_cancel_url;
    
    protected $_sdk;

	public function __construct($options = null) 
    {
		App_Options::setOptions($this, $options);		
		$facebook = new _Facebook(array(
		  'appId'  => $this->_app_id,
		  'secret' => $this->_app_secret,
		  'cookie' => true, // enable optional cookie support
		));							
		$this->_sdk = $facebook;		
	}

	public function me($object = '')
	{		
		$user = $this->_sdk->getUser();
		
		if (!$user)
			$this->redirectToLogin();
		
		if (!empty($object))
		        $object = '/' . strtolower($object);		
		try {				
		    $result = $this->api('/me' . $object);		   	    
		} catch (FacebookApiException $e) {
			echo $e; die;
		}		
		return $result;
	}
	
	// возможно неплохо переделать в public
	protected function api($request) {
	    try {	    	  	
			$result = $this->_sdk->api($request);			
		} catch (FacebookApiException $e){			 // если поймали эксепшен делаем редирект на странице входа 		
			$session = App_Resource::get('session'); // (возможно что пользователь удалил приложение, а куки остались)
			if (!isset($session->checkFacebookLogout)) {
				$session->checkFacebookLogout = 1;
				$this->redirectToLogin();  
			} else 
				unset($session->checkFacebookLogout);
			$result = $this->_sdk->api($request);	 // возможно вызывает бесконечный цикл
		}
		return $result;
	}
    
	public function parse_signed_request($signed_request) 
	{
	    list($encoded_sig, $payload) = explode('.', $signed_request, 2);	
	    // decode the data
	    $sig  = $this->_base64_url_decode($encoded_sig);
	    $data = json_decode($this->_base64_url_decode($payload), true);	
	    if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
	        error_log('Unknown algorithm. Expected HMAC-SHA256');
	        return null;
	    }	
	    // check sig
	    $expected_sig = hash_hmac('sha256', $payload, $this->_app_secret, $raw = true);
	    if ($sig !== $expected_sig) {
	        error_log('Bad Signed JSON signature!');
	        return null;
	    }	
	    return $data;
	}
	
	protected function _base64_url_decode($input) {
	    return base64_decode(strtr($input, '-_', '+/'));
	}
	
	protected function redirectToLogin()
	{		
		$options  = $this->getLoginOptions();			
		$loginUrl = $this->_sdk->getLoginUrl($options);
		Zend_Controller_Front::getInstance()->getResponse()->setRedirect($loginUrl)->sendResponse();		
		die('Редирект на facebook не сработал!');
	}

	protected function getLoginOptions()
	{
		$options = array();
		if (!empty($this->_app_perm))
		    $options['scope'] = $this->_app_perm;
		if (!empty($this->_cancel_url))
		    $options['cancel_url'] = $this->_cancel_url;
		    
		return $options;
	} 
	
	///////////////////
    // Сетеры и гетеры      
    ///////////////////

	/**
	 * @return the $_app_id
	 */
	public function getApp_id() {
		return $this->_app_id;
	}

	/**
	 * @return the $_app_secret
	 */
	public function getApp_secret() {
		return $this->_app_secret;
	}

	/**
	 * @return the $_app_perm
	 */
	public function getApp_perm() {
		return $this->_app_perm;
	}

	/**
	 * @param field_type $_app_id
	 */
	public function setApp_id($_app_id) {
		$this->_app_id = $_app_id;
	}

	/**
	 * @param field_type $_app_secret
	 */
	public function setApp_secret($_app_secret) {
		$this->_app_secret = $_app_secret;
	}

	/**
	 * @param field_type $_app_perm
	 */
	public function setApp_perm($_app_perm) {
		$this->_app_perm = $_app_perm;
	}

   	/**
	 * @return the $_cancel_url
	 */
	public function getCancel_url() {
		return $this->_cancel_url;
	}

	/**
	 * @param field_type $_cancel_url
	 */
	public function setCancel_url($_cancel_url) {
		$this->_cancel_url = $_cancel_url;
	}
	
    
}