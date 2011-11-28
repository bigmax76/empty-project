<?php
class App_Common_Array
{
    public static function toAssoc($array, $key, $multi = false)
	{			
		$result = array();
		if (!$multi) {
			foreach ($array as $value) {				
				$result[$value[$key]] = $value;
			}
		} else {
		    foreach ($array as $value) {				
				$result[$value[$key]][] = $value;
			}
		}		
		return $result;
	}
	
    /**
	 * Extract single col from a list
	 */
	public static function getCol($array, $key, $duplicates = false)
	{
		$asResult = array();
		foreach ($array as $value) 
			if (isset($value))
				$asResult[] = $value[$key];
		
		return $duplicates ? $asResult : array_unique($asResult);
	}
}