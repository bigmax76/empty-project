<?php
class App_Service_Foursquare
{
	const AUTH_URL = 'https://gowalla.com/api/oauth/new';					// страница авторизации пользователя	const GM_URL = 'http://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false&language=en';		//урл для получения адреса в стандартном формате	const SESSION = 'FOURSQUARE_ACCESS_TOKEN';
	protected $_consumer_key    = null;    protected $_consumer_secret = null;    protected $_callback_url    = null;     protected $_request_token   = null;	protected $_access_token    = null;
	public function __construct($options = null) {		App_Options::setOptions($this, $options);
	}
	/**
	 * Возвращает access token из сессии или делает запрос
	 */
	protected function getAccessToken()	{		if($this->_access_token!==null)			return $this->_access_token;		if(isset($_SESSION[self::SESSION]))		{			$this->_access_token = $_SESSION[self::SESSION];		}		else		{			$this->requestAccessToken();		}
		return $this->_access_token;	}
	protected function setAccessToken($token)	{		$this->_access_token = $token;		$_SESSION[self::SESSION]  = $token;	}		protected function getRequestToken()	{		if($this->_request_token!==null)			return $this->_request_token;
		if(isset($_GET['code']))    	{			$this->_request_token = $_GET['code'];			return $this->_request_token;;		}		$this->requestRequestToken();	}
/**
 * Requests
 */
	protected function requestRequestToken()
	{
		$redirect_to = AUTH_URL.'?redirect_uri=' . $this->_callback_url . '&client_id=' . $this->_consumer_key;
		
		$this->facebook->redirect($redirect_to);	//TODO Facebook redirect or just header("Location:") 
		
	}
	
	/**
	 * меняем request token на access token  и сохраняем его в сeссии 
	 */
	protected function requestAccessToken()
	{
		$requestToken = $this->getRequestToken();
		
		// меняем request token на access token 
		$client = new Zend_Http_Client();
		$client->setUri('https://foursquare.com/oauth2/access_token');
	    $client->setMethod(Zend_Http_Client::GET);
		$client->setParameterGet(array(
		        'grant_type'    => 'authorization_code',
		        'client_id'     => $this->_consumer_key,
		        'client_secret' => $this->_consumer_secret,
		        'code'          => $requestToken,
		        'redirect_uri'  => $this->_callback_url,
	    ));		    		    
		$response = $client->request();
		if($response->isError())
			throw new Exception('Error using Foursquare API');

		$result = Zend_Json::decode($response->getBody());
		
		// Access tokens истекает после 2 недель
		$this->setAccessToken($result['access_token']);		
	}
	
	/**
	 * Возвращает все checkins текущего пользователя 
	 */
	private function getCheckins()
	{
		$client = new Zend_Http_Client();
		$client->setUri('https://api.foursquare.com/v2/users/self/checkins');		
	    $client->setMethod(Zend_Http_Client::GET);
	    	$params = array('oauth_token' => $this->getAccessToken());
		$client->setParameterGet($params);
		$response = $client->request();
		
		if($response->isError())
		{
			throw new Exception("Error getting checkins from Forsquare.com");
		}
		
		$result = Zend_Json::decode($response->getBody());
		return $result;
	}
	
	/**
	 * Возвращает отметки текущего юзера для конкретного места
	 */
	public function getPlaceCheckins($place_name)
	{
		// получаем все отметки текущего пользователя
		$checkins = $this->getCheckins();
		$checkins = $checkins['response']['checkins']['items'];
		// находим среди них отметки относящиеся к нужному месту
		$place_name = mb_strtolower($place_name);
		$result = array();
		foreach ($checkins as $checkin) {
			$name = mb_strtolower($checkin['venue']['name']);
			if ($name == $place_name)
			    $result[] = $checkin;
		}
		return $result;
		
	}
	
	/**
	 * Возвращает места вблизи точки с координатами 
	 * (радиус в метрах)
	 */
	public function venues($lat, $lng, $radius = 50)
	{
		$client = new Zend_Http_Client();
		$client->setUri('https://api.foursquare.com/v2/venues/search');		
	    $client->setMethod(Zend_Http_Client::GET);
	    //$client->setHeaders('X-Gowalla-API-Key', $this->_consumer_key);	
	    //$client->setHeaders('Accept', 'application/json');	
		$client->setParameterGet(array(
		        'll'          => $lat . ',' . $lng,	
		        'llAcc'       => $radius,	
		        'limit'       => 50,	
		        'oauth_token' => $this->getAccessToken(),	             	            	        
	    ));	
		$response = $client->request();
		if ($response->getStatus() == 200) {
			$result = Zend_Json::decode($response->getBody());
			return $result;
		}
		return array();
	}	

	public function getEventCheckins($event, $matchingMode)
	{
		$formattedLocation = $this->getFormattedLocation(array($event['Event']['country'], $event['Event']['state'], $event['Event']['zip'], $event['Event']['city'], $event['Event']['address']));
		
		
		$checkins = $this->getCheckins();
		$items = $checkins['response']['checkins']['items'];
		
		$take = array();
		foreach($items as $item)
		{
			if($this->isCheckinMatchEvent($item, $event, $matchingMode, $formattedLocation))
				$take[] = $item;
		}
		

		return $take;
	}
		/**
		 * 
		 * @param unknown_type $checkin
		 * @param unknown_type $event
		 * @param int $matchingMode - 1 - loose, 2 - more strict, 3 - GMaps
		 */
		private function isCheckinMatchEvent($checkin, $event, $matchingMode, $formattedLocation=null)
		{
			if($matchingMode==1)
				$eventDurationAverage = 60*60*4;	//4h
			else
				$eventDurationAverage = 60*60*2;	//2h
				
					
			$isMatch = true;


			//1. date and time
				//$event['Event']['date'] = '2011-03-04';
				//$event['Event']['time'] = '21:34:03';
			if($event['Event']['time']=='00:00:00')
			{
				$isMatchDate = ($event['Event']['date']==date('Y-m-d', $checkin['createdAt']));
			}
			else
			{
				$ts = strtotime($event['Event']['date'].' '.$event['Event']['time']);
				$isMatchDate = abs($checkin['createdAt'] - $ts)<$eventDurationAverage;
			}

			
			
			
			
			//2. Location
			
			
			if($event['Event']['location']==Event::TYPE_WORLDWIDE)
			{
				$isMatchLocation = true;
			}
			else
			{
				if(!isset($checkin['venue']['location']['country']))
					$checkin['venue']['location']['country'] = null;
					
				if(!isset($checkin['venue']['location']['state']))
					$checkin['venue']['location']['state'] = null;
					
				if(!isset($checkin['venue']['location']['postalCode']))
					$checkin['venue']['location']['postalCode'] = null;
					
				if(!isset($checkin['venue']['location']['city']))
					$checkin['venue']['location']['city'] = null;
					
				if(!isset($checkin['venue']['location']['address']))
					$checkin['venue']['location']['address'] = null;
				
				$useGoogleMaps = ($matchingMode==3);
				if($useGoogleMaps)
				{
					$formattedLocationCheckin = $this->getFormattedLocation(array($checkin['venue']['location']['country'], $checkin['venue']['location']['state'], $checkin['venue']['location']['postalCode'], $checkin['venue']['location']['city'], $checkin['venue']['location']['address']));
					$isMatchLocation = ($formattedLocationCheckin==$formattedLocation);
				}
				else
				{
					//array('USA','United States');
				
					$isMatchLocation = ($event['Event']['country']==$checkin['venue']['location']['state']);
					$isMatchLocation = ($this->getShortStateName($event['Event']['state'])==$checkin['venue']['location']['state']);				
					$isMatchLocation = $isMatchLocation && (empty($event['Event']['zip']) || ($event['Event']['zip']==$checkin['venue']['location']['postalCode']));
					$isMatchLocation = $isMatchLocation && (empty($event['Event']['city']) || ($event['Event']['zip']==$checkin['venue']['location']['city']));
						
					if($matchingMode==2)
					{
						$addressMatchValue = $this->getStringsMatch($event['Event']['address'], $checkin['venue']['location']['address']);
						$crossingMatchValue = $this->getStringsMatch($event['Event']['intersection'], $checkin['venue']['location']['crossStreet']);
						
						$isMatchLocation = $isMatchLocation && ($addressMatchValue+$crossingMatchValue)>0.5;
						
						//$isMatchLocation = $isMatchLocation && (empty($event['Event']['address']) || ($event['Event']['address']==$checkin['venue']['location']['address']));
						//$isMatchLocation = $isMatchLocation && (empty($event['Event']['intersection']) || ($event['Event']['intersection']==$checkin['venue']['location']['crossStreet']));
					}
				}
			}
			

			
			//3. title
			if($matchingMode==2)
			{
				$matchTitleValue = $this->getStringsMatch($event['Event']['name'], $checkin['venue']['name']);
				$isMatchTitle = $matchTitleValue>0.4;
			}
			else
			{
				$isMatchTitle = true;
			}

			
			$isMatch = $isMatchDate && $isMatchLocation && $isMatchTitle;
			
			return $isMatch;
		}
		
		private function getStringsMatch($string, $string2)
		{
			$min = min(strlen($string), strlen($string2));
			$lev = levenshtein($string, $string2);
			
			if($min==0 || $lev>$min)
				return 0;
			
			$match = ($min - $lev)/$min;

			return $match;
		}
		
		private function getShortStateName($long)
		{
			$short = $long;
			
			return $short;
		}
	
		private function getFormattedLocation($locationData)
		{
			$locationString = urlencode(join(' ', $locationData));
			$response = json_decode(file_get_contents(sprintf(self::GM_URL, $locationString)));
			
			if($response->status=='OK')
				$formattedLocation = $response->results[0]->formatted_address;
			else
				$formattedLocation = null;
			
			return $formattedLocation;
		}
		
		
/**
 * Compatibility
 */
	/**
	 * @return the $_access_token
	 */
	public function getAccess_token()
	{
		return $this->getAccessToken();
	}
	/**
	 * @param field_type $_access_token
	 */
	public function setAccess_token($_access_token) {
		$this->_access_token = $_access_token;
	}
	
	/**
	 * @return the $_request_token
	 */
	public function getRequest_token()
	{
		return $this->getRequestToken();
	}
	/**
	 * @param field_type $_request_token
	 */
	public function setRequest_token($_request_token)
	{
		$this->_request_token = $_request_token;
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
	
}

