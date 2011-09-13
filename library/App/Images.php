<?php
class App_Images
{
	static $white_list = array(
	         'image/jpeg',
	         'image/gif',
	         'image/png',
	        );
	        
	/**
	 *  Осуществляет проверку загруженных файлов на 
	 *  соответствие требованиям к изображению.
	 *  Необходимо для обеспечения безопасной загрузки изображения
	 *  на входе элемент массива $_FILES
	 */
	static public function isValid($file)
	{		
		// проверка содержимого файла (если getimagesize ничего не вернет - значит это не картинка)
		$imageinfo = getimagesize($file['tmp_name']);
		
		// проверка Content-Type 
		if ( !in_array($imageinfo['mime'], self::$white_list)){
			return false;
		}
		return true;		
	}
	
	static public function createDir($dir){
		// если папка еще не была создана
		if (!file_exists($dir)) {
			// чтобы папки рекурсивно создавались под Windows - необходимо использование обратных слешей
     	    $dir = str_replace('/', '\\', $dir);
     	    mkdir ($dir, 0755, true);     	    
		}
	}
}