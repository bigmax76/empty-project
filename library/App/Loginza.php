<?php
class App_Loginza extends Zend_Mail
{
	protected $loginza_api = 'http://loginza.ru/api/authinfo';
	
	// запрошенный token 
	protected $token;
	
	// response body строкой 
	protected $body;
	
	// response Array 
	protected $response;
		
	// экземпляр Zend_Log
	protected $logger      = Null;
	
	
	/**
	 * Отправка запроса на loginzu
	 */
	public function request($token)
	{		
		$this->token = $token;
		$client = new Zend_Http_Client($this->loginza_api);		
		$client->setMethod(Zend_Http_Client::GET);
		$client->setParameterGet('token', $token);
		$response = $client->request();
		$this->body     = $response->getBody();
		$this->response = json_decode($this->body);
		//echo '<pre>'; print_r($this->body); echo '</pre>';
		//echo '<pre>'; print_r(json_decode($this->body)); echo '</pre>';
		return $this->response;
	}
	
	/**
	 * логирование объекта ответа для упрощения тестирования
	 */
	public function log()
	{
		$string = '$token = ' . $this->token . ' ' . $this->body;			
		$writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/loginza.log');
        $logger = new Zend_Log($writer); 			
	    $logger->log($string, Zend_Log::INFO);
	}   

	
	/**
	 * Заполняем объект дополнительными данными 
	 */
	public function getExtendedData(Model_User $user)
	{
		$data = $this->response;
		//echo '<pre>$data$data'; print_r($data); echo '</pre>';
		
		if(isset($data->nickname))
		    $user->nickname = $data->nickname;
		
		if(isset($data->name->first_name))
		    $user->first_name = $data->name->first_name;
		    
		if(isset($data->name->last_name))
		    $user->last_name = $data->name->last_name;    
		    
		if(isset($data->email))
		    $user->email  = $data->email;
		    
		if(isset($data->gender))
		    $user->gender = $data->gender;  
		    
		if(isset($data->dob))
		    $user->dob = $data->dob;   
		
		if(isset($data->language))
		    $user->language = $data->language;   
		
		if(isset($data->address->home->country))
		    $user->country = $data->address->home->country;

		if(isset($data->photo))
		    $user->photo = $data->photo; 
		    
		return $user;
	}
}