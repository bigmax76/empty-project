<?php
class App_OpenId_Service
{
	
	protected static $_openid_provider_names = array(
		'Google' => 'google.com',
	    'Yahoo'  => 'yahoo.com',
	);
	
	
	
	/**
	 * Возвращает имя провайдера openid по переданному идентификатору
	 * @param unknown_type $open_id
	 */
	public static function getProviderName($open_id)
	{
		foreach (self::$_openid_provider_names as $key => $value) {
			if (stripos($open_id, $value))
			    return $key;
		}
		return $open_id; 
	}
}