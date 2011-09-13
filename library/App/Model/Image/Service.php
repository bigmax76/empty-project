<?php
class App_Model_Image_Service
{		
	static $white_list = array(
	         'image/jpeg',
	         'image/gif',
	         'image/png',
	        );

	// формат файлов в хранилище 
	static $storeFormat = 'jpg';
	
	/**
	 *  Проверка что файл является изображением 
	 *  принимает как абсолютный путь, так и элемент массива $_FILES 
	 */
	static public function isImage($file)
	{		
		if (is_array($file) && isset($file['tmp_name']))
			$file = $file['tmp_name'];
		
		if (!file_exists($file))
			throw new Exception('File not exists!');
		
		// если getimagesize ничего не вернет - значит это не картинка
		$imageinfo = getimagesize($file);
		
		// проверка Content-Type 
		if (!in_array($imageinfo['mime'], self::$white_list)){
			return false;
		}
		return true;		
	}
	
	static public function createDir($dir, $perm = 0755) {
		// если папка не существует
		if (!file_exists($dir)) {
			// чтобы папки рекурсивно создавались под Windows - необходимо использование обратных слешей
			if (App_Common_Service::is_windows())
     	        $dir = str_replace('/', '\\', $dir);
     	    mkdir ($dir, $perm, true);     	    
		}
	}
	
    static public function getDir($file) {
		$info = pathinfo($file);		
        return $info['dirname'];
	}
	
	/*
	 * Формирует путь к файлу по его id в базе данных
	 * (без file extansion)
	 */
	static public function getNameById($id) {
		$number = sprintf('%06d', $id);
		$uri = '/' .
		//  substr($number, 6, 2) . '/' . 
		  substr($number, 4, 2) . '/' .
		  substr($number, 2, 2) . '/' .
		  $number;
		return $uri;
	}
	
}