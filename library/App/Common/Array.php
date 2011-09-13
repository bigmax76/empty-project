<?php
class App_Common_Array
{
    public static function toAssoc(array $array, $key)
	{			
		$result = array();
		foreach ($array as $value) {				
			$result[$value[$key]] = $value;
		}
		return $result;
	}
	
	/**
	 * Тоже что и toAssoc только нормализует ключи
	 */
	/*public static function toAssocNormalize(array $array, $key)
	{		
		$result = array();
		foreach ($array as $value) {
			$index = str_replace(' ', '_', strtolower($value[$key]));			
			$result[$index] = $value;
		}
		return $result;
	}*/
	
    /**
	 * Extract single col from a list
	 */
	public static function getCol(array $array, $key, $duplicates = false)
	{
		$asResult = array();
		foreach ($array as $value) 
			if (isset($value))
				$asResult[] = $value[$key];
		
		return $duplicates ? $asResult : array_unique($asResult);
	}
}