<?php
class App_Users
{
	protected $actions = array(
		'read', 'edit',
	);
	
	protected $group = array('guest', 'members', 'admin');
	
	/**
	 * Проверка прав доступа текущего пользователя к объекту
	 */
	public static function checkAccess($obj, $action)
	{
		$user = Zend_Registry::get('user');
		$role = $user->role;
		if (empty($role))
			$role = 'guest';
		if($obj->isAllowByGroup($role)) 
			return true;
		
		if($obj->isAllowByUser($user->id)) 
			return true;
		
		return false;		
	}
	
	
}