<?php
/*
 *  Плагин позволяющий осуществить basic/digest аутентификацию. 
 *  
 *  Если php установлен не как Apache Module (например Zend Server)
 *  необходимо добавить следующее правило в .htaccess 
 *  RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
 *  а точнее (в случаее zend framework)
 *  RewriteRule !\.(js|ico|gif|jpg|png|css|xml|xslt)$ index.php [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
 */
class App_Controller_Plugins_HttpAuth extends Zend_Controller_Plugin_Abstract {

    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) 
    {
    	$path = APPLICATION_PATH . '/configs/access.txt';
        $resolver = new Zend_Auth_Adapter_Http_Resolver_File($path);

        $config = array(
            'accept_schemes' => 'basic',
            'realm'          => 'Entry your user login and password',
            'nonce_timeout'  => 3600,
        );

        $adapter = new Zend_Auth_Adapter_Http($config);

        $adapter->setBasicResolver($resolver);

        $request = $this->getRequest();
        $response = $this->getResponse();

        assert($request instanceof Zend_Controller_Request_Http);
        assert($response instanceof Zend_Controller_Response_Http);

        $adapter->setRequest($request);
        $adapter->setResponse($response);

        $result = $adapter->authenticate();
        
        if (!$result->isValid()) {        	
            $response->sendResponse();            
            // Bad userame/password, or canceled password prompt
            exit;
        }         
    }
}