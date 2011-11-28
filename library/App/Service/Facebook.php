<?php
require_once("Facebook/facebook.php");
class App_Service_Facebook extends Facebook
{
    protected $_scope;
    protected $_canvasPage;
    protected $_canvasUrl;

	protected $_friendsFull = array();
    protected $_friendsIds = array();
    protected $_friendsApp = array();

	protected $_fields = "uid, name";

	protected $_info = null;

	protected $_permissions = null;

	protected $_requiredPermissions = "";

	public function __construct($options = null)
    {
    	if ($options instanceof Zend_Config)
    	    $options = $options->toArray();
    	//echo '<pre>'; print_r($options); echo '</pre>';    
    	$this->_scope      = $options['scope'];
    	$this->_canvasPage = $options['canvasPage'];
    	$this->_canvasUrl  = $options['canvasUrl'];

    	parent::__construct($options);    			
	}

	public function getJSOptions()
	{
		return array(
			'oauth' => true,
			'appId' => $this->appId,
			'status' => true,
			'cookie' => true,
			'xfbml' => true,
			'authResponse' => null
		);
	}

	protected function _sessionSave($key, $value)
    {
        if (!session_id()) session_start();

        $key = 'fb_' . $this->appId . '_' . $key;
        $_SESSION[$key] = $value;
    }

    protected function _sessionLoad($key)
    {
        if (!session_id()) session_start();

        $key = 'fb_' . $this->appId . '_' . $key;
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

	protected function _loadFriends($user_uid, $clean = false)
    {
        $session = $this->_sessionLoad('friendsData_' . $user_uid);
        if ($clean || empty($session))
        {
            try
            {
                // Uses FQL because Graph doesn't provide user info
                $fql = "SELECT {$this->_fields}, is_app_user FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1=" . addslashes($user_uid) . ")";
	            $data = $this->api(array('method' => 'fql.query', 'query' => $fql));

	            /*$data = $this->api('fql', 'GET', array('q' => $fql));
	            if (!empty($data['data']))
	                $data = $data['data'];*/

                foreach ($data as $key => $value)
                    $data[$key]['id'] = $value['uid'];

                $session = $data;
                $this->_sessionSave('friendsData_' . $user_uid, $data);
            }
            catch (Exception $e)
            {
				error_log($e->getMessage());
                $session = null;
            }
        }

        if (empty($this->_friendsFull[$user_uid]))
        {
            $this->_friendsFull[$user_uid] = $session;
            $this->_friendsIds[$user_uid] = array();
            $this->_friendsApp[$user_uid] = array();

            if ($this->_friendsFull[$user_uid])
            foreach ($this->_friendsFull[$user_uid] as $value)
            {
                $this->_friendsIds[$user_uid][] = $value['uid'];
                if ($value['is_app_user'])
                    $this->_friendsApp[$user_uid][] = $value['uid'];
            }
        }
    }

	public function getFriends($user_uid, $clean = false, $full = false)
    {
        if ($clean || empty($this->_friendsFull[$user_uid]))
            $this->_loadFriends($user_uid, $clean);

        return $full ? $this->_friendsFull[$user_uid] : $this->_friendsIds[$user_uid];
    }

	public function getAppFriends($user_uid, $clean = false)
    {
        if ($clean || empty($this->_friendsFull[$user_uid]))
            $this->_loadFriends($user_uid, $clean);

        return $this->_friendsApp[$user_uid];
    }

	// сеттеры и геттеры
	
	public function getScope() {
		return $this->_scope;
	}

	public function getCanvasPage() {
		return $this->_canvasPage;
	}

	public function getCanvasUrl() {
		return $this->_canvasUrl;
	}

	public function setScope($_scope) {
		$this->_scope = $_scope;
	}

	public function setCanvasPage($_canvasPage) {
		$this->_canvasPage = $_canvasPage;
	}

	public function setCanvasUrl($_canvasUrl) {
		$this->_canvasUrl = $_canvasUrl;
	}   




	public function iframeRedirect($url)
    {
        ?><!DOCTYPE HTML><html><body><script type="text/javascript">top.location.href = '<?= $url ?>';</script></body></html><?php
        die();
    }

	public function setRequiredPermissions($permissions)
    {
        if (is_array($permissions))
            $permissions = join(",", $permissions);
        $this->_requiredPermissions = $permissions;
    }

    public function getRequiredPermissions($array = false)
    {
        return $array ? explode(",", $this->_requiredPermissions) : $this->_requiredPermissions;
    }

	public function getPermissions($array = false, $exceptions = false)
	{
		try {
			if ($this->_permissions == null)
			{
				$result = $this->api('me/permissions');
				if (isset($result['data']) && isset($result['data'][0]))
					$this->_permissions = $result['data'][0];
			}
			return $this->_permissions;
		}
		catch (Exception $e)
		{
			$this->_sessionValid = false;
			if ($exceptions)
				throw $e;
			else
				return array();
		}
	}

	public function getInfo($exceptions = false)
	{
		try {
			if ($this->_info == null)
				$this->_info = $this->api('/me');
			return $this->_info;
		}
		catch (Exception $e)
		{
			$this->_sessionValid = false;
			if ($exceptions)
				throw $e;
			else
				return array();
		}
	}

	public function getUserApiInfo($user_uid = null)
	{
		try {
			return $this->api('/' . $user_uid);
		}
		catch (Exception $e)
		{
			error_log($e->getMessage());
			return array();
		}
	}

	public function getUserRestInfo($user_uid, $fields)
	{
		try {
			if (is_array($fields)) $fields = join(",", $fields);

			if (is_array($user_uid)) $user_uid = "uid IN (". join(",", $user_uid) . ")";
			else $user_uid = "uid = " . $user_uid;

			$result = $this->api(
					array('method' => 'fql.query',
						'query' => "SELECT $fields FROM user WHERE " . $user_uid));
			return $result;
		}
		catch (Exception $e)
		{
			error_log($e->getMessage());
			return array();
		}
	}

	public function checkPermissions($required, $exceptions = false)
    {
        $this->setRequiredPermissions($required);
        $permissions = $this->getPermissions(true, $exceptions);

        if (!is_array($required))
            $required = explode(',', $required);

        foreach ($required as $permission)
        {
            $permission = trim($permission);
            if (empty($permissions[$permission]))
            {
                if ($exceptions)
                    throw new Exception('Missed permission ' . $permission, 0);
                else
                    return false;
            }
        }
        return true;
    }

    public function checkLogin($permissions = "", $loadUserInfo = true)
    {
        $request = $this->getSignedRequest();
        if ($request)
            $this->_sessionSave('signed_request', $request);

        try
        {
            $user_id = $this->getUser();
            if (!empty($user_id))
            {
                if ($loadUserInfo)
                    $this->getInfo(true);

                if ($permissions)
                    $this->checkPermissions($permissions, true);

                return $user_id;
            }
        }
        /*catch (FacebookApiException $e)
        {
			error_log($e->getMessage());
        }*/
        catch (Exception $e)
        {
			error_log($e->getMessage());
            // null
        }
        return null; // Exception - Invalid Session for User $this->getSignedRequest()['user_id'];
    }

    public function requireLogin($url, $permissions = "", $loadUserInfo = true)
    {
        if ($res = $this->checkLogin($permissions, $loadUserInfo))
            return $res;

        if ($url)
        {
            $permissions_str = is_array($permissions) ? join(",", $permissions) : $permissions;
            $this->iframeRedirect($this->getLoginUrl(array('scope' => $permissions_str, 'redirect_uri' => $url)));
        }
        return null;
    }

    public function getRequiredAppUrl()
    {
	    $base = parse_url($this->_canvasUrl);
	    $base = $base['path'];
	    return $this->_canvasPage . substr($_SERVER['REQUEST_URI'], strlen(rtrim($base, "/") . "/"));
    }

}