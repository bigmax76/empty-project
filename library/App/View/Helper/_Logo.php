<?php
/**
 * Хелпер позволяющий выводить логотип для переданной персональной страницы
 * @author таргет
 *
 */
class App_View_Helper_Logo extends Zend_View_Helper_Abstract
{
	private $shop;
	private $width;
	private $height;	
	
	private function img($file)
	{
		$fileinfo = pathinfo($file);
		$path = $this->shop->getImgDir().'/'. $fileinfo['basename'];		   
		return '<img src="'.$path.'" width ="'.$this->width.'" height="'.$this->height.'" />';
	}
	
	public function logo(Model_Shop $shop, $width = 100, $height = 60 )
	{
		
		$this->width  = $width;
		$this->height = $height;
		$this->shop   = $shop;
		
		// если имеется нужный размер - отдаем его				
		$pattern = $shop->getImgDir() . '/logo-' . $width .'x'.$height.'*';
		foreach (glob($pattern) as $file) {			
			return $this->img($file); 		    
		}
		
		// иначе если имеется исходный файл - создаем картинку с нужным размером и отдаем ее 
	    $pattern = $shop->getImagesDir() . '/logo.*';
		foreach (glob($pattern) as $source)
		{
			$fileinfo = pathinfo($source);
			//echo '<pre>'; print_r($fileinfo);echo'</pre>';
			$target = $shop->getImagesDir() . '/logo-' . $width . 'x' . $height . '.' . $fileinfo['extension'];
			if ($this->img_resize($source, $target, $width, $height))
			{
				return $this->img($target);
			}   
		}
		// иначе ничего не делаем		
	}
	
	/**
	 * Функция осуществляющая ресайз исходного изображения
	 * подсмотрена на http://www.php5.ru/articles/image#size
	 * 
	 * @param unknown_type $src      - имя исходного файла
	 * @param unknown_type $dest     - имя генерируемого файла
	 * @param unknown_type $width    - ширина генерируемого изображения, в пикселях
	 * @param unknown_type $height   - высота генерируемого изображения, в пикселях
	 * @param unknown_type $rgb      - цвет фона, по умолчанию - белый
	 * @param unknown_type $quality  - качество генерируемого JPEG, по умолчанию - максимальное (100)
	 * @return в случае успешного выполнения возвращает true, иначе false
	 */
	private function img_resize($src, $dest, $width, $height, $rgb=0xFFFFFF, $quality=100)
	{
		if (!file_exists($src)) return false;
	
	    $size = getimagesize($src);
	
	    if ($size === false) return false;
	
	    // Определяем исходный формат по MIME-информации, предоставленной
	    // функцией getimagesize, и выбираем соответствующую формату
	    // imagecreatefrom-функцию.
	    $format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
	    $icfunc = "imagecreatefrom" . $format;
	    if (!function_exists($icfunc)) return false;
	
	    $x_ratio = $width / $size[0];
	    $y_ratio = $height / $size[1];
	
        $ratio       = min($x_ratio, $y_ratio);
	    $use_x_ratio = ($x_ratio == $ratio);
	
	    $new_width   = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
	    $new_height  = !$use_x_ratio ? $height : floor($size[1] * $ratio);
	    $new_left    = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
	    $new_top     = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
	
	    $isrc = $icfunc($src);
	    $idest = imagecreatetruecolor($width, $height);
	
	    imagefill($idest, 0, 0, $rgb);
	    imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0, 
	       $new_width, $new_height, $size[0], $size[1]);
	
	    imagejpeg($idest, $dest, $quality);
	
	    imagedestroy($isrc);
	    imagedestroy($idest);
	
	    return true;	
	}
}