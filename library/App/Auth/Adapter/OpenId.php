<?php

/**
 * Enter description here ...
 * @author bigmax
 *
 */
class App_Auth_Adapter_OpenId implements Zend_Auth_Adapter_Interface
{
    /**
     * The identity value being authenticated
     *
     * @var string
     */
    protected $_id = null;

    /**
     * Reference to an implementation of a storage object
     *
     * @var Auth_OpenID_OpenIDStore
     */
    protected $_storage = null;

    /**
     * The URL to redirect response from server to
     *
     * @var string
     */
    protected $_returnTo = null;

    /**
     * The HTTP URL to identify consumer on server
     *
     * @var string
     */
    protected $_root = null;

    /**
     * Extension object or array of extensions objects
     *
     * @var string
     */
    protected $_extensions = null;

    /**
     * The response object to perform HTTP or HTML form redirection
     *
     * @var Zend_Controller_Response_Abstract
     */
    protected $_response = null;

    /**
     * Enables or disables interaction with user during authentication on
     * OpenID provider.
     *
     * @var bool
     */
    protected $_check_immediate = false;
   
    /**
     * Constructor
     *
     * @param string $id the identity value
     * @param Auth_OpenID_OpenIDStore $storage an optional implementation
     *        of a storage object
     * @param string $returnTo HTTP URL to redirect response from server to
     * @param string $root HTTP URL to identify consumer on server
     * @param mixed $extensions Auth_OpenID_Extension extension object or array of extensions objects
     * @param Zend_Controller_Response_Abstract $response an optional response
     *        object to perform HTTP or HTML form redirection
     * @return void
     */
    public function __construct($id = null,
                                Auth_OpenID_OpenIDStore $storage = null,
                                $returnTo = null,
                                $root = null,
                                $extensions = null,
                                Zend_Controller_Response_Abstract $response = null) { 
        $this->_id         = $id;
        $this->_storage    = $storage;
        $this->_returnTo   = $returnTo;
        $this->_root       = $root;
        $this->_extensions = $extensions;
        $this->_response   = $response;
    }
    
    /**
     * Authenticates the given OpenId identity.
     * Defined by Zend_Auth_Adapter_Interface.
     *
     * @throws Zend_Auth_Adapter_Exception If answering the authentication query is impossible
     * @return Zend_Auth_Result
     */
    public function authenticate() {
        
        $id = $this->_id;        
        $consumer = new Auth_OpenID_Consumer($this->_storage);
        
        if (!empty($id)) 
        {
            // шаг первый
            $auth_request = $consumer->begin($id);            
            if (!$auth_request) {
                return new Zend_Auth_Result(Zend_Auth_Result::FAILURE, $id, array("Authentication failed", 'Not a valid OpenID'));
            }
            
            // запрос дополнительных данных ax            
            $this->getAxExtension($auth_request); 
            $this->getSregExtension($auth_request);   
            
            if (Auth_OpenID::isFailure($auth_request)) {
                return new Zend_Auth_Result(
                        Zend_Auth_Result::FAILURE,
                        $id,
                        array("Authentication failed", "Could not redirect to server: " . $auth_request->message));
            }           
            
            $redirectUrl = $auth_request->redirectUrl($this->_root, $this->_returnTo);
            
           
            
            if (Auth_OpenID::isFailure($redirectUrl)) {
                return new Zend_Auth_Result(
                        Zend_Auth_Result::FAILURE,
                        $id,
                        array("Authentication failed", $redirectUrl->message));
            }
                      
            Zend_OpenId::redirect($redirectUrl);
            
        } else {
             // step two
            $response = $consumer->complete(Zend_OpenId::selfUrl());
            
            switch($response->status) {
                    // This means the authentication was cancelled.
                case Auth_OpenID_CANCEL :   
                	// Authentication failed; display the error message.  
                case Auth_OpenID_FAILURE:    return new Zend_Auth_Result(
						                            Zend_Auth_Result::FAILURE,
						                            null,
						                            array("Authentication failed. " . @$response->message));						                            
						                     break;                    
                case Auth_OpenID_SUCCESS:
                    return $this->_constructSuccessfulResult($response);
                break;
            }
        }
    }
    
    /**
     * @param Auth_OpenID_ConsumerResponse $response
     * @return Zend_Auth_Result
     */
    protected function _constructSuccessfulResult(Auth_OpenID_ConsumerResponse $response)
    {
    	//echo '<pre>$response'; print_r($response); echo '</pre>';
    	        
        $identity = array();        
        $identity['openid_identity'] = $response->getDisplayIdentifier();
        
        if ($response->endpoint->canonicalID) {
            $identity['openid_op_endpoint'] = $response->endpoint->canonicalID;    
        }
       
        // получаем массив дополнительных полей
        $fields = $this->getExtensionFields($response);
        foreach ($fields as $key => $value) {
        	$identity[$key] = $value;
        }       
              
        // не понятно пока что за pаpe  возможно нужно перенести в getExtensionFields       
        if ($pape = Auth_OpenID_PAPE_Response::fromSuccessResponse($response)) {
            $identity['pape'] = (array)$pape;
        }        
        return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $identity, array("Authentication successful"));
    }
   
    /**
     * Sets the storage implementation which will be use by OpenId
     *
     * @param  Auth_OpenID_OpenIDStore $storage
     * @return Smapp_Auth_Adapter_OpenId Provides a fluent interface
     */
    public function setStorage(Auth_OpenID_OpenIDStore $storage)
    {
        $this->_storage = $storage;
        return $this;
    }
    
    /**
     * Sets the value to be used as the identity
     *
     * @param  string $id the identity value
     * @return Zend_Auth_Adapter_OpenId Provides a fluent interface
     */
    public function setIdentity($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * Sets the HTTP URL to redirect response from server to
     *
     * @param  string $returnTo
     * @return Zend_Auth_Adapter_OpenId Provides a fluent interface
     */
    public function setReturnTo($returnTo)
    {
        $this->_returnTo = $returnTo;
        return $this;
    }

    /**
     * Sets HTTP URL to identify consumer on server
     *
     * @param  string $root
     * @return Zend_Auth_Adapter_OpenId Provides a fluent interface
     */
    public function setRoot($root)
    {
        $this->_root = $root;
        return $this;
    }

    /**
     * Sets OpenID extension(s)
     *
     * @param  mixed $extensions
     * @return Zend_Auth_Adapter_OpenId Provides a fluent interface
     */
    public function setExtensions($extensions)
    {
        $this->_extensions = $extensions;
        return $this;
    }

    /**
     * Sets an optional response object to perform HTTP or HTML form redirection
     *
     * @param  string $root
     * @return Zend_Auth_Adapter_OpenId Provides a fluent interface
     */
    public function setResponse($response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * Enables or disables interaction with user during authentication on
     * OpenID provider.
     *
     * @param  bool $check_immediate
     * @return Zend_Auth_Adapter_OpenId Provides a fluent interface
     */
    public function setCheckImmediate($check_immediate)
    {
        $this->_check_immediate = $check_immediate;
        return $this;
    }
    
    /**
     * Запрос допольнительных данных в формате Attribute Exchange
     * @param unknown_type $auth_request
     */
    protected function getAxExtension($auth_request)
    {
    	// внимание этот запрос постояння причина глюков 
    	// не следует запрашивать больше данных чем реально необходимо
    	$attribute = array();    	
        foreach ($this->_extensions as $attrib) {
        	switch ($attrib) {
        		case 'nickname':  $attribute['nickname'] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/friendly'     ,1,1, 'nickname');
        		                  break;
        		case 'email'   :  $attribute['email']    = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/contact/email'           ,1,1, 'email');
        		                  break;
        		case 'fullname':  $attribute['fullname'] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson'              ,1,1, 'fullname');
        		                  break;
        		case 'dob'     :  $attribute['dob']      = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/birthDate'               ,1,1, 'dob');
        		                  break;
        		case 'gender'  :  $attribute['gender']   = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/person/gender'           ,1,1, 'gender');
        		                  break;
        		case 'postcode':  $attribute['postcode'] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/contact/postalCode/home' ,1,1, 'postcode');
        		                  break;
        		case 'country' :  $attribute['country']  = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/contact/country/home'    ,1,1, 'country');
        		                  break;
        		case 'language':  $attribute['language'] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/pref/language'           ,1,1, 'language');
        		                  break;
                case 'timezone':  $attribute['timezone'] = Auth_OpenID_AX_AttrInfo::make('http://axschema.org/pref/timezone'           ,1,1, 'timezone');
        		                  break;       		                  
        		default:          break;
        		break;
        	}
        }
	
		// Create AX fetch request
		$ax = new Auth_OpenID_AX_FetchRequest;
		
		// Add attributes to AX fetch request
		foreach($attribute as $attr){
		        $ax->add($attr);
		}
    	$auth_request->addExtension($ax);
    }
    
    /**
     * Запрос допольнительных данных в формате Sreg 
     * @param unknown_type $auth_request
     */
    protected function getSregExtension($auth_request)
    {
    	//echo '<pre>$this->_extensions'; print_r($this->_extensions); echo '</pre>';
        $sreg_request = Auth_OpenID_SRegRequest::build(
	                                     // Required
	                                     $this->_extensions, // array('nickname'),
	                                     // Optional
	                                     array() // array('fullname', 'email'));
	    );
	    $auth_request->addExtension($sreg_request);
	   
    }
    
    /**
     * возвращает массив дополнительных полей
     * @param unknown_type $response
     */
    protected function getExtensionFields($response)
    {
    	// если есть sreg - возвращаем sreg
        if ($sreg = Auth_OpenID_SRegResponse::fromSuccessResponse($response)) {
        	$fields = $sreg->contents();
            if (!empty($fields))
            	return $fields;
        }
    	
        // иначе возвращаем ax 
        if ($ax   = Auth_OpenID_AX_FetchResponse::fromSuccessResponse($response)) {
        	//echo '<pre>'; print_r($ax); echo '</pre>';
        	$data = $ax->data;
        	foreach ($data as $key => $value)
        	{
        		switch ($key) {
        			case 'http://axschema.org/namePerson/friendly': $result['nickname'] = $value[0];
        				break;
        			case 'http://axschema.org/contact/email'      :	$result['email']    = $value[0];
        				break;       													  
        			case 'http://axschema.org/namePerson'         : $result['fullname'] = $value[0];
        				break;
        			case 'http://axschema.org/birthDate'          : $result['dob']      = $value[0];
        			    break;
        			case 'http://axschema.org/person/gender'      : $result['gender']   = $value[0];
        				break;
        			case 'http://axschema.org/contact/postalCode/home' : $result['postcode'] = $value[0];
        			    break;
        			case 'http://axschema.org/contact/country/home'    : $result['country']  = $value[0];
        				break;
        			case 'http://axschema.org/pref/language'      : $result['language'] = $value[0];
        			    break;
        			case 'http://axschema.org/pref/timezone'      : $result['timezone'] = $value[0];
        				break;
        			default:
        				break;
        		}
        	}      
        	return $result; 	
        }        
        // иначе возврашаем пустой массив
        return array();
    }
}