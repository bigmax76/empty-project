<?php
class App_Vote_Action_Up extends App_Vote_Action_Abstract
{
	protected $_dbTableName = 'app_votes';
	protected $_dbTable     = null;
	
	protected $_user            = null;
	protected $_user_reputation = null;
	
	protected static $actionNone        = 0;
	protected static $actionUp          = 1;
	protected static $actionUpDisable   = 2;
	protected static $actionDown        = 3;
	protected static $actionDownDisable = 4;
	protected static $actionFlag        = 5;
	protected static $actionFlagDisable = 6;
	
	public function __construct(App_Model_Abstract $user = null, $options = null)
	{
		$this->_user = $user;
	}
	
	public function vote($node_id = null, $action_id = null)
	{
	}
	

 
}