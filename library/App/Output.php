<?php
class App_Output
{
	protected static $end_of_line = null;
	
	/**
	 * поставляет нужный символ перевода строки
	 */
	public static function send($message) 
	{		
		$back_sp = (!empty($_SERVER['argv'])) ? self::getEOL() : '<br />';
		$message = $message . $back_sp;
		echo $message;
        @ob_flush();flush();	 
	}
	
	
	/**
	 * отдает "правильный" символ перевода строки в зависимости от OS 
	 */
	public static function getEOL() 
	{
		if (null === self::$end_of_line) {
			self::$end_of_line = (file_exists('C:\Windows')) ? "\r\n" : "\n";
		}
		return self::$end_of_line;
	} 
}