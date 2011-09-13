<?php
// для работы необходима библиотека PHPThumb
require_once 'PHPThumb/ThumbLib.inc.php';

// App_Image_Store 
class App_Model_Image_Store
{
	// путь к хранилищу
	protected $_storeDir;
	
	// формат файлов в хранилище
	protected $_storeFormat = 'jpg';
	
	// имя базы данных хранилища
	protected $_dbTableName;
	
	// store instance
	protected $_dbTable;
	
	// создавать ли таблицу - хранилище если она не существует
	protected $_createTableIfNotExists = false;
	
	// массив доступных форматов
	protected $_format = array();

	public function __construct($options = array()) {
		App_Options::setOptions($this, $options);
		$this->_storeDir = APPLICATION_PATH . $this->_storeDir;	
	}
	
	/*
	 * Сохраняет картинку в хранилище, делает запись о ней в бд
	 * и возвращает id по которой она будет доступна
	 * (основной метод ресурса)
	 * 
	 * Принимает абсолютный путь к файлу. 
	 * Eсли не задан сохраняет все images из $_FILES.
	 * Если их нет - бросается исключение. 
	 */
	public function addImage($parent_id, $file = null, $type = null)
	{		
		$files = $this->getFiles($file);
		foreach ($files as $file) {
			if (!file_exists($file))
			    throw new Exception('File not exists!');
			if (!App_Model_Image_Service::isImage($file))
		        throw new Exception('Image file expected!');
		    // делаем запись в бд    
		    $data = $this->getImageInfo($file);
		    $data['parent_id'] = $parent_id;
		    $data['type']      = $type;
		    $id = $this->getDbTable()->insert($data);
		    // сохраняем файл в хранилище
			$target = $this->getFileById($id);
			$dir    = App_Model_Image_Service::getDir($target);
			App_Model_Image_Service::createDir($dir);			
			$image = PhpThumbFactory::create($file);
			$image->save($target);
		}
		return $id;
	}
	
	/**
	 * Удаляет файл по его id (физически и из базы)
	 */
	public function delete($id) {
		$file  = $this->getFileById($id);
		$where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);
		$this->getDbTable()->delete($where);		
		unlink($file);
	} 
	
	/**
	 * Возвращает абсолютный путь к файлу по его id
	 */
	public function getFileById($id) {
		$name = App_Model_Image_Service::getNameById($id);
		$name = $this->_storeDir . $name . '.' . $this->_storeFormat;	
		return $name;
	}
	
	protected function getFiles($file = null)
	{
		$files = array();
		if (!empty($file)) {
			$files[] = realpath($file);
			return $files;
		}
		if (empty($_FILES)) 
			return array();
	    foreach ($_FILES as $file) {
			if ($file['error'] != 0)
				continue;
			$files[] = $file['tmp_name'];
		}
		return $files;
	}
	
	/*
	 * Получаем параметры картинки. 
	 */
	protected function getImageInfo($file)
	{		
		$info = getimagesize($file);		
		$result = array();
		$result['width']  = $info['0'];
		$result['height'] = $info['1'];
		$result['size']   = filesize($file);
		return $result;
	}

    ///////////////////////
    // сеттеры и геттеры //
    ///////////////////////
    
    public function getStoreDir() {
		return $this->_storeDir; 
	}
	
	public function setStoreDir($dir) {
		if (!is_dir($dir))
			throw new Exception('Не задано хранилище для ресурса ImageManager! Указанная папка не существует!'); 
		$this->_storeDir = $dir;
		return $this;
	}
    
	public function getStoreFormat() {
		return $this->_storeFormat; 
	}
	
    public function setStoreFormat($format) {
		if (!in_array($format, array('jpg', 'gif', 'png')))
			throw new Exception('Неверно указан формат файлов для ресурса ImageManager! Формат не задан или не поддерживается!'); 
		$this->_storeFormat = $format;
		return $this;
	}
	
	public function getDbTableName() {
		if (null === $this->_dbTableName) {			
		   throw new Exception('Не указано имя таблицы (хранилища) для ресурса ImageManager');	
		}		
		return $this->_dbTableName;
	}
	
	public function setDbTableName($name = null) {
		if (!is_string($name))
			throw new Exception('Неверно задано имя таблицы (хранилища) для ресурса ImageManager!');
		$this->_dbTableName = $name;
		return $this;	 
	}
	
	public function getDbTable() {
		if (null === $this->_dbTable) {			
			$table = new Zend_Db_Table();			
			$table->setOptions(array(
			    'name'        => $this->getDbTableName(),
				'primary'     => 'id',
			    'rowsetClass' => 'App_ImageManager_Rowset'
			 
			));		
			$this->_dbTable = $table;
		}
		return $this->_dbTable;
	}
	
	public function getCreateTableIfNotExists() {
		return $this->_createTableIfNotExists;
	}
	
    public function setCreateTableIfNotExists($value) {
		$this->_createTableIfNotExists = (boolean) $value;
		return $this;
	}
	
	public function getFormat() {
		return $this->_format;
	}
	
    public function setFormat($format) {
		$this->_format = $format;
		return $this;
	}

}