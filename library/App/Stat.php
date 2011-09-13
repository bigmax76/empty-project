<?php
class App_Stat
{
	static protected $start_time  = null;		
	static protected $checkpoints = null;	
	static protected $is_started  = false;
	static protected $is_disable  = false;
	static protected $decimals    = 4;    // число знаков после запятой
	
	/**
	 * Запуск сбора статистики
	 */
	public static function start() {		
		if (self::$is_started)
			throw new Exception('Сбор статистики уже запущен!');
		self::$is_started = true;
		self::$start_time = microtime(true);
		self::$checkpoints[] = array('name'=> 'Старт работы скрипта', 'time' => microtime(true)); 		
	}
	
	/**
     * Отключает вывод статистики
     */
    public static function disable() {
    	self::$is_disable = true;
    } 
	
	/**
	 * Установка контрольной точки 
	 */
	public static function set($name = null) {		   
		if($name == null) 
			throw new Exception('Не задано название контрольной точки!');
		self::$checkpoints[] = array( 'name'=> $name , 'time' => microtime(true));		
	}
	
	/**
	 * Установка контрольной точки (обратная совместимость)
	 */
	public static function setPoint($name = null) {
		self::set($name);
	}
	
    /**
     * Отображение собранных данных
     */
    public static function show($showPublic = false) {    			    
		if (self::isAjax())
    		return;
    	if ($showPublic)
		    self::showPublicInfo();
		   
		if(!self::$is_disable || APPLICATION_ENV == 'localhost') { 
		    self::showCheckPointInfo();
		    self::showDbInfo();
		}
		echo '<div style="clear:both"></div>';
	}
	
	protected static function isAjax() {
		return Zend_Controller_Front::getInstance()->getRequest()->isXmlHttpRequest();
	}
	
 	/**
 	 * Простой вывод времени генерации страницы
 	 */
 	protected static function showPublicInfo() { 
        $time = microtime(true) - self::$start_time;	
		echo '<span style="color: #999999; font-size: 7px;">' . $time . '</span>';
 	}
	
	/**
	 * Вывод результатов по контрольным точкам
	 */
	protected static function showCheckPointInfo()
	{				
		self::$checkpoints[] = array( 'name'=> 'Конец работы скрипта' , 'time' => microtime(true));
		echo '<div style="margin:15px 0px;float:left;padding:5px;text-align:left; background-color:#f0f0f0;">';
		echo "<table class='stat' border='1'>";

		$last_point = self::$start_time;
		$points = self::$checkpoints;
		$count  = count($points) - 1;
		for ($i = 0; $i < $count; $i++) {
			echo "<tr><td>".$points[$i]['name']."</td><td>";
			echo "<td>&nbsp;&nbsp;</td><td>";			
			echo number_format($points[$i]['time'] - self::$start_time,self::$decimals);
			echo "</td><td> &nbsp;&nbsp;&nbsp; </td><td>";	
			echo number_format($points[$i+1]['time'] - $points[$i]['time'],self::$decimals);
			$last_point = $points[$i]['time'];
			echo "</td></tr>\n";			
		}	
						
		echo '<tr style="background-color:#cccccc; font-weight: bold;">			   
		        <td >Всего времени: </td>
		        <td></td>
		        <td></td>
				<td>' . number_format($points[$count]['time'] - self::$start_time,self::$decimals) . '</td>';	
		echo '</tr>';
		echo '</table>';
		echo '</div>';
		
		
	}
	
	/**
	 * Вывод результатов по базе данных
	 */
	protected static function showDbInfo()
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		if (!$db instanceof Zend_Db_Adapter_Abstract)
			return;
		$profiler   = $db->getProfiler();
		$totalTime  = $profiler->getTotalElapsedSecs();
		$queryCount = $profiler->getTotalNumQueries();
		
		$profiles = $profiler->getQueryProfiles();
		if (!empty($profiles))
		{
			echo '<div style="margin:15px 25px;float:left;padding:5px;text-align:left; background-color:#f0f0f0; border:1px solid #aaaaaa">';
			echo 'Общее колличество произведенных запросов: '. $queryCount . '<br />';
			echo 'Общее время выполнения запросов: '         . $totalTime  . '<br />';
			echo '<h3 style="margin-top:8px;border-bottom:1px solid #aaaaaa; padding-bottom:3px">Подробная статистика:</h3>';
			echo '<table>';			
			foreach ($profiles as $profile) {			        		         
			        echo '<tr><td><b>Запрос:</b></td><td>'      . $profile->getQuery()       . '</td></tr>';			       
			        echo '<tr><td>Время выполнения: </td><td> ' . $profile->getElapsedSecs() . '</td></tr>';			        
			}
			echo '</table>';
			echo '</div>';
			echo '<div style="clear:both"></div>';
		}
	}
	
}