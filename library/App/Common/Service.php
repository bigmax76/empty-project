<?php
class App_Common_Service
{
    
	/**
	 * Разбивает строку на подстроки + удаляет пустые строки и пробелы в начале и конце полученных строк
	 * (для памяти PHP_EOL - перевод строки)
	 */
	public static function explodeTrim($string, $separator = ','){
		$result = array();
		$strings = explode($separator, $string);		
		foreach ($strings as $item) {
			if (!empty($item))
				$result[] = trim($item);
		}					
		return $result;
	}
	
	/**
	 * Последние средство чтобы удалить "непослушные" файлы в Windows
	 */
	public static function unlink($file)
	{
		// если файл не удаляется обычным способом
		if (!unlink($file)){
			// пробуем удалить его командой windows
			if (self::is_windows()) { // если это windows конечно
				$file = new SplFileInfo($file);		
	            $real_path = $file->getRealPath();
	            unset($file);				
				exec("DEL /F /Q " . $real_path, $lines, $errno);
				echo '<pre>$lines'; print_r($lines); echo '</pre>';
		        echo '<pre>$errno'; print_r($errno); echo '</pre>';
		        sleep(3);
			}
		}
	}
	
	/**
	 * Возвращает true если script запущен под Windows
	 */
	public static function is_windows()
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			return true;
		return false;
	}
}

