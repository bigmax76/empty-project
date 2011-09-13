<?php
class App_Color_Gd
{	
    /**
     * Функция возвращает основной цвет изображения
     * в привычном для css шестнадцатиричном формате
     */
	
    public static function getBaseColor($img_path)
	{
		// проверяем наличие файла
		if (!file_exists($img_path)) {
			echo 'qwertyui';
			return false;
		}   
		
		// создаем новое и открываем переданное изображение 
		$target = imagecreatetruecolor(1, 1);
		$source = self::openImage($img_path);
		
		// уменьшаем  изображение до размеров 1x1 пиксель
		$target_width  = 1;
		$target_height = 1;	
		list($sourse_width, $sourse_height) = getimagesize($img_path);
		
		imagecopyresized($target, $source, 0, 0, 0, 0, $target_width, $target_height, $sourse_width, $sourse_height);
		// получаем цвет точки с координатами x,y
		$rgb = ImageColorAt($target, 0, 0);	
		// удаляем временное изображение	
		ImageDestroy($target);
		return  dechex($rgb);			
	}
	
    /**
     *  Открывает изображение средствами gd 
     *  (просто в зависимости от типа файла нужно использовать разные функции)
     */
    public static function openImage($img_path) 
    {
        # JPEG:
        $im = @imagecreatefromjpeg($img_path);
        if ($im !== false) { return $im; }

        # GIF:
        $im = @imagecreategif($img_path);
        if ($im !== false) { return $im; }

        # PNG:
        $im = @imagecreatepng($img_path);
        if ($im !== false) { return $im; }

        # GD File:
        $im = @imagecreategd($img_path);
        if ($im !== false) { return $im; }

        # GD2 File:
        $im = @imagecreategd2($img_path);
        if ($im !== false) { return $im; }

        # WBMP:
        $im = @imagecreatewbmp($img_path);
        if ($im !== false) { return $im; }

        # XBM:
        $im = @imagecreatexbm($img_path);
        if ($im !== false) { return $im; }

        # XPM:
        $im = @imagecreatexpm($img_path);
        if ($im !== false) { return $im; }

        # Попытка открыть со строки:
        $im = @imagecreatestring(file_get_contents($img_path));
        if ($im !== false) { return $im; }

        return false;
    }
	
	
	
}