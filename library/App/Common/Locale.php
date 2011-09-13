<?php
class App_Common_Locale
{
	/**
     * Returns the language part of the locale
     */	
	public static function getLanguage($locale)
	{
		$locale = explode('_', $locale);
        return $locale[0];
	}
	
	/**
     * Returns the region part of the locale if available     
     */	
    public static function getRegion($locale)
    {
        $locale = explode('_', $locale);
        if (isset($locale[1]) === true) {
            return $locale[1];
        }
        return false;
    }
}