<?php 
/*
 * Смысл класса в том, чтобы скрыть стек вызова при выводе исключения
 * (так как при этом виден логин и пароль к базе)
 */
class App_Model_Pdo_Adapter extends PDO
{    
	public function __construct($dsn, $username, $password, $options)
	{	
		try {
			parent::__construct($dsn, $username, $password, $options);
			$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->exec("SET CHARACTER SET utf8");
		} catch (Exception $e) {
			echo 'Message: ' .$e->getMessage();
			die;			
		}
	}
}