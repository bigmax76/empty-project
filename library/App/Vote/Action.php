<?php
class App_Vote_Action
{
	public static $None        = 0;
	public static $Up          = 1;
	public static $UpDisable   = 2;
	public static $Down        = 3;
	public static $DownDisable = 4;
	public static $Flag        = 5;
	public static $FlagDisable = 6;
	
/*	public static function factory($action_id)
	{
		if ($action_id == self::$Up)
			return new App_Vote_Action_Up();

		if ($action_id == self::$Down)
			return new App_Vote_Action_Down();
		
	}
*/
}