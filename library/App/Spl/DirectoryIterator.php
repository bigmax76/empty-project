<?php 
/*
 * Расширение DirectoryIterator
 */
class App_Spl_DirectoryIterator extends DirectoryIterator
{
	// получение расширения файла
    public function getExtension() {
    	if (!$this->isFile())
    		return;
    	$path = pathinfo($this->getRealPath());
    	if (isset($path["extension"]))
    	    return $path["extension"];	
    	return;
   }
}