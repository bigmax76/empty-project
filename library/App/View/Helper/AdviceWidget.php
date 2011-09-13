<?php
class App_View_Helper_AdviceWidget extends Zend_View_Helper_Abstract
{
	public function adviceWidget($notice)
	{		
	    if (!empty($notice))
		{
			$advice_html = '<div id = "help" style="margin-top:10px">'
						  .'		<b style="color:#3F8000"> СОВЕТЫ И ПОДСКАЗКИ </b>'
						  . 		$notice
						  .'</div>';
			
			return $advice_html;
		}		   
	}
}