<?php
/**
 *  Хелпер для задание js переменных из контроллера 
 *  (выводится все собранное App_Controller_Action_Helter_JsVars)
 */
class App_View_Helper_JsVars extends Zend_View_Helper_Url
{
    public function jsVars()
    {
        $content = $this->view->placeholder('jsVars');        
        if (!empty($content)) 
        	$content = "<script type=\"text/javascript\">\n //<![CDATA[ \n" . $content .  " //]]>\n</script>"; 
        
        return $content;    
    }
}