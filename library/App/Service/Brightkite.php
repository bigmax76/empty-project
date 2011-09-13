<?php



class App_Service_Brightkite
{
	const AUTH_URL = 'http://brightkite.com/oauth';					// страница авторизации пользователя
	//const GM_URL = 'http://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false&language=en';		//урл для получения адреса в стандартном формате
	//const ADDRESS_LANGUAGE = 'en';
	const CONSUMER_KEY = 'YmFiNDU0MjY2ZmQ4YTUzY2I5NDQ1ODY4';
	const SECRET_KEY = 'NTI2Yzk0NDYyZWNjYWVkMTRiYTVlNWM5';
	
	const SESSION_REQUEST_TOKEN = 'BRIGHTKITE_REQUEST_TOKEN';
	const SESSION_ACCESS_TOKEN = 'BRIGHTKITE_ACCESS_TOKEN';
	
	
	private $config = null;
	protected $_request_token   = null;
	protected $_access_token    = null;
	/*protected $_consumer_key    = null;
    protected $_consumer_secret = null;
    protected $_callback_url    = null; 
*/

	
		 
	
	
	public function __construct($config)
	{
		$this->config = $config;
	}

	public function getCallbackUrl()
	{
		$callbackUrl = $this->config->siteurl.'checkins/brightkite?controller='.$_REQUEST['controller'].'&itemId='.$_REQUEST['itemId'];
		
		return $callbackUrl;
	}
	
	private function getConsumer()
	{
		$options = array(
			'consumerKey'          => self::CONSUMER_KEY,
			'consumerSecret'       => self::SECRET_KEY,
			'callbackUrl'          => $this->getCallbackUrl(),
			'siteUrl'              => self::AUTH_URL,		    	      
			//'requestTokenUrl'      => '' ,           // url для запроса Request Token    ( по умолчанию siteUrl+'/request_token' )				
			//'accessTokenUrl'       => '',            // url для запроса Access Token     ( по умолчанию siteUrl+'/access_token' )	
			//'authorizeUrl'         => '',            // url для авторизации пользователя ( по умолчанию siteUrl+'/authorize' )
			//'userAuthorizationUrl' => '',            // то же что и authorizeUrl (очевидно обратная совместимость ) 
			//'requestMethod'        => '',            // GET ИЛИ POST (по умолчанию POST)
	        //'rsaPrivateKey'        => '',
	        //'rsaPublicKey'         => '',
	        //'signatureMethod'      => '',  
			//'version'              => '',			 
        );
        
		$consumer = new Zend_Oauth_Consumer($options);
        
		return $consumer;
	}
	
	
	
	
	
	/**
	 * Возвращает access token из сессии или делает запрос
	 */
	protected function getAccessToken()
	{
		if($this->_access_token!==null)
			return $this->_access_token;
		
		if(isset($_SESSION[self::SESSION_ACCESS_TOKEN]))
		{
			$this->_access_token = $_SESSION[self::SESSION_ACCESS_TOKEN];
		}
		else
		{
			$this->requestAccessToken();
		}
		
		
		return $this->_access_token;
	}
	
	protected function setAccessToken($token)
	{
		$this->_access_token = $token;
		$_SESSION[self::SESSION_ACCESS_TOKEN]  = $token;
	}
	
	protected function getRequestToken()
	{
		if($this->_request_token===null)
		{
			$this->_request_token = $this->requestRequestToken();
		}
		
		return $this->_request_token;
	}
	

	
	
	
	
	
/**
 * Requests
 */
	private function requestRequestToken()
	{
		$consumer = $this->getConsumer();
		
        $token = $consumer->getRequestToken();
        
        
        
		$_SESSION[self::SESSION_REQUEST_TOKEN] = serialize($token);
        $consumer->redirect();
        /*return $token;*/
	}
	
	/**
	 * меняем request token на access token  и сохраняем его в сeссии 
	 */
	private function requestAccessToken()
	{
		$requestToken = $this->getRequestToken();
		
		$consumer = $this->getConsumer();
		
		echo __FILE__."<pre>\n";
		var_dump($_REQUEST);
		echo "</pre>\n--END--";
		exit;
		
		$token = $consumer->getAccessToken($_GET, $requestToken);
		
		echo __FILE__."<pre>\n";
		var_dump($token);
		echo "</pre>\n--END--";
		exit;
		
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
		/*$client = new Zend_Http_Client();
		$client->setUri('https://api.foursquare.com/v2/users/self/checkins');		
	    $client->setMethod(Zend_Http_Client::GET);*/
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
	
	
	
	public function getEventCheckins($event, $matchingMode)
	{
		//$formattedLocation = $this->getFormattedLocation(array($event['Event']['country'], $event['Event']['state'], $event['Event']['zip'], $event['Event']['city'], $event['Event']['address']));
		
		
		$checkins = $this->getCheckins();
		//$items = $checkins['response']['checkins']['items'];
		
		$take = array();
		/*foreach($items as $item)
		{
			if($this->isCheckinMatchEvent($item, $event, $matchingMode, $formattedLocation))
				$take[] = $item;
		}*/
		

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

		
		
	
}

