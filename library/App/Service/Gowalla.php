<?php 
class App_Service_Gowalla
{
	protected $_consumer_key    = null;
    protected $_consumer_secret = null;
    protected $_callback_url    = null; 
    protected $_request_token   = null;
	protected $_access_token    = null;

	// страница авторизации пользователя
	protected $_auth_url = 'https://gowalla.com/api/oauth/new';	 
	
	/**
	 * @return the $_callback_url
	 */
	public function getCallback_url() {
		return $this->_callback_url;
	}

	/**
	 * @param field_type $_callback_url
	 */
	public function setCallback_url($_callback_url) {
		$this->_callback_url = $_callback_url;
	}

	public function __construct($options = null)
	{
		App_Options::setOptions($this, $options);
		//echo '<pre>$this'; print_r($this); echo '</pre>';
	}
	
	/**
	 * Возвращает access token из сессии или делает запрос на Gowalla
	 */
	protected function getAccessToken()
	{
		if (null == $this->_access_token) {					
			if (!isset($_SESSION['GOWALFA_ACCESS_TOKEN'])){
				// Todo Дописать получениие access токен 
				$token = $this->getRequestToken();
				die('getAccessToken - die');
			}			
			$this->_access_token = $_SESSION['GOWALFA_ACCESS_TOKEN'];
		}
		return $this->_access_token;
	}
	
	protected function getRequestToken()
	{
		// Это заготовка функции на будующее (если понадобится)
		
		// если ответ от сервиса уже пришел - возвращаем его
		if (isset($_GET['code']))
			return $_GET['code'];
		
		// иначе запрашиваем request token								
		$redirect_to = $this->_auth_url . '?redirect_uri=' . $this->_callback_url . '&client_id=' . $this->_consumer_key;
		echo '$redirect_to = ' . $redirect_to;
		$this->facebook->redirect($redirect_to);	
	}
	 
	/**
	 * @return the $_consumer_key
	 */
	public function getConsumer_key() {
		return $this->_consumer_key;
	}

	/**
	 * @return the $_consumer_secret
	 */
	public function getConsumer_secret() {
		return $this->_consumer_secret;
	}

	/**
	 * @return the $_request_token
	 */
	public function getRequest_token() {
		return $this->_request_token;
	}

	/**
	 * @return the $_access_token
	 */
	public function getAccess_token() {
		return $this->_access_token;
	}

	/**
	 * @param field_type $_consumer_key
	 */
	public function setConsumer_key($_consumer_key) {
		$this->_consumer_key = $_consumer_key;
	}

	/**
	 * @param field_type $_consumer_secret
	 */
	public function setConsumer_secret($_consumer_secret) {
		$this->_consumer_secret = $_consumer_secret;
	}

	/**
	 * @param field_type $_request_token
	 */
	public function setRequest_token($_request_token) {
		$this->_request_token = $_request_token;
	}

	/**
	 * @param field_type $_access_token
	 */
	public function setAccess_token($_access_token) {
		$this->_access_token = $_access_token;
	}

	/**
	 * Возвращает места вблизи точки с координатами 
	 * (радиус в метрах)
	 */
	public function spots($lat, $lng, $radius = 50)
	{
		$client = new Zend_Http_Client();
		$client->setUri('https://api.gowalla.com/spots');		
	    $client->setMethod(Zend_Http_Client::GET);
	    $client->setHeaders('X-Gowalla-API-Key', $this->_consumer_key);	
	    $client->setHeaders('Accept', 'application/json');	
		$client->setParameterGet(array(
		        'lat'          => $lat,	
		        'lng'          => $lng,	
		        'radius'       => $radius,	
		        'oauth_token'  => $this->getAccessToken(),	         	            	        
	    ));	
		$response = $client->request();
		if ($response->getStatus() == 200) {
			$result = Zend_Json::decode($response->getBody());
			return $result;
		}
		return array();
	}	
    
	
	/**
	 * Возвращает данные места с именем $name расположеном
	 * вблизи точки с координатами (радиус в метрах) 
	 */
	public function spotByName($name, $lat, $lng, $radius = 50)
	{
		$result = $this->spots($lat, $lng, $radius);		
	    $name = trim(mb_strtolower($name));		
		foreach ($result['spots'] as $spot) {
			$spot_name = trim(mb_strtolower($spot['name']));
			if ($spot_name != $name)
				continue;					
			return $spot;		
		}
		return array();
	}
	
	public function getSpotIdByActivityUrl($url)
	{
		$res = explode('/',$url);
		return $res[2];		
	}
	
    /**
     * Так как gowalla не передает id записи явно - извлекаем ее из переданных url 
     */
    public function getIdByUrl($url)
	{
		$res = explode('/',$url);
		foreach ($res as $str) {
			if (is_numeric($str))
				return $str;
		}
		return null;		
	}
	
	/**
	 * Возвращает данные текущего пользователя
	 */
	public function me()
	{
		$client = new Zend_Http_Client();
		$client->setUri('https://api.gowalla.com/users/me');		
	    $client->setMethod(Zend_Http_Client::GET);
	    $client->setHeaders('X-Gowalla-API-Key', $this->_consumer_key);	
	    $client->setHeaders('Accept', 'application/json');	
		$client->setParameterGet(array(		        
		        'oauth_token'  => $this->getAccessToken(),	         	            	        
	    ));	
		$response = $client->request();
		if ($response->getStatus() == 200) {
			$result = Zend_Json::decode($response->getBody());
			return $result;
		}
		return array();
	}
	
	/**
	 * Возвращает chekins для конкретного места
	 */
	public function getSpotCheckins($spot_id)
	{
	    $client = new Zend_Http_Client();
		$client->setUri('https://api.gowalla.com/spots/' . $spot_id .'/events');		
	    $client->setMethod(Zend_Http_Client::GET);
	    $client->setHeaders('X-Gowalla-API-Key', $this->_consumer_key);	
	    $client->setHeaders('Accept', 'application/json');	
		$client->setParameterGet(array(		
		        'page'        => 1,     
		        'per_page'    => 100, 		        
		        'oauth_token' => $_SESSION['GOWALFA_ACCESS_TOKEN'],				            	            	        
	    ));	
		$response = $client->request();
		//echo '<pre>$response'; print_r($response); echo '</pre>';
		if ($response->getStatus() == 200) {
			$result = Zend_Json::decode($response->getBody());
			$res = array();
			foreach ($result['activity'] as $row ) {
				if ($row['type'] != 'checkin')
				    continue;	
				$res[] = $row;				
			}
			return $res;				
		}
		return array();
	}
	
	/**
	 * Возвращает checkins для конкретного места и конкретного usera
	 */
	public function getSpotCheckinsByUser($spot_id, $user_url)
	{
		$checkins = $this->getSpotCheckins($spot_id);
		$result   = array();
		foreach ($checkins as $checkin) {
			if ($checkin['user']['url'] == $user_url)
				$result[] = $checkin;			
		}
		return $result;
	}
	
	/**
	 * меняем request token на access token  и сохраняем его в сeссии 
	 */
	public function requestAccessToken($request_token)
	{
		// меняем request token на access token 
		$client = new Zend_Http_Client();			
		$client->setUri('https://api.gowalla.com/api/oauth/token');		
	    $client->setMethod(Zend_Http_Client::POST);		
		$client->setParameterGet(array(
		        'grant_type'    => 'authorization_code',	
		        'client_id'     => $this->_consumer_key,	
		        'client_secret' => $this->_consumer_secret,	
		        'code'          => $request_token,			
		        'redirect_uri'  => $this->_callback_url,	            	        
	    ));		    		    
		$response = $client->request();				
		//echo '<pre>$response'; print_r($response); echo '</pre>';			
		$result = Zend_Json::decode($response->getBody());
		echo '<pre>$result'; print_r($result); echo '</pre>';	
		
		// Access tokens истекает после 2 недель
		$_SESSION['GOWALFA_ACCESS_TOKEN']  = $result['access_token'];
		$_SESSION['GOWALFA_REFRESH_TOKEN'] = $result['refresh_token'];
	}
}