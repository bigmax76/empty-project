<?php
class App_Mail extends Zend_Mail
{
	public function __construct($charset = 'UTF-8')
	{
		parent::__construct($charset);
		$this->setFrom('info@maxbuild.kh.ua' , 'admin');
	}
	
	public function setBody($script_name , $param = array())
	{
		// определяем шаблон для письма
		$layout = new Zend_Layout(array(
			'layoutPath' => APPLICATION_PATH . '/layouts'
		));
		$layout->setLayout('email');
		
		// создаем вид для содержимого письма и передаем в него параметры
		$view = new Zend_View();	
		$view->setScriptPath(APPLICATION_PATH . '/mail');	
		foreach ($param as $kay =>$value) {
			$view->assign($kay, $value);
		}	
		// получаем и устанавливаем итоговый html в сегмент body 	
		$layout->content = $view->render($script_name . '.phtml');
		$html = $layout->render();
		
		$this->setBodyHtml($html);
		return $this;
		//теперь остается только вызвать send(в клиенском коде)
	}
}