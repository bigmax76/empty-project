<?php
class App_View_Helper_YandexMap extends Zend_View_Helper_Abstract
{
	 public function yandexMap()	 
    {
    	$cx = 36.229065;
    	$cy = 49.980149;
    	$ch = 10;
    	$name = 'Хата Ламината';
    	$adress = 'ул.Плехановская, 2/5';
    	//$adress = 'пр. Ленина, 52';
    	
    	$this->view->headscript()->appendFile('http://api-maps.yandex.ru/1.1/index.xml?key=AGJ6XksBAAAA-m2UfAIAD-4LCvSs-W8VAwi6nybQ9rHujjUAAAAAAAAAAADeCx8wHnZi3tYz3-YeXKmQJVw5Qg==');
		$this->view->headscript()->appendFile('/public/js/ymap.js');
		$this->view->headScript()->appendScript('// Создание обработчика для события window.onLoad
		    YMaps.jQuery(function () 
		    	{
		    		// Создает экземпляр карты и привязывает его к созданному контейнеру
		            var map = new YMaps.Map(YMaps.jQuery("#YMapsID")[0]);

		            // Создание объекта геокодера
                    var geocoder = new YMaps.Geocoder("Харьков ' . $adress . '", {results: 1});
                    
                    // или так Запускает процесс геокодирования
                    // var geocoder = new YMaps.Geocoder(new YMaps.GeoPoint(37.588395, 55.762718), {results: 1});
                    
                                    
                    // По завершению геокодирования инициализируем карту первым результатом
		            YMaps.Events.observe(geocoder, geocoder.Events.Load, function (geocoder) {
		                if (geocoder.length()) {
		                   //map.addOverlay(geocoder); 	
		                   // Установка для карты ее центра и масштаба	                  
		                   map.setBounds(geocoder.get(0).getBounds());	
		                   
		                   // Создание перетаскиваемой метки
                           var placemark = new YMaps.Placemark(map.getCenter(), {draggable: true});
                           map.addOverlay(placemark);
                           placemark.setIconContent("' . $name . '");
                           placemark.setBalloonContent("<strong>Адрес: </strong>' . $adress . '<br/>");
                           
                           // центрируем метку по завершении перетаскивания
                           YMaps.Events.observe(placemark, placemark.Events.DragEnd, function () {
						        map.setCenter(placemark.getGeoPoint());
						   });
						   YMaps.Events.observe(map, map.Events.Update, function () {
						        var form = document.forms["form"];
						        form.lat.value = map.getCenter().getLat();
						        form.lng.value = map.getCenter().getLng();
						        form.lower_lat.value = map.getBounds().getLeftBottom().getLat();
						        form.lower_lng.value = map.getBounds().getLeftBottom().getLng();
						        form.upper_lat.value = map.getBounds().getRightTop().getLat();
						        form.upper_lng.value = map.getBounds().getRightTop().getLng();
						    });
						   
						   
                           // Общее расстояние и предыдущая точка
                           var distance = 0, prev;
                           
                           // Установка слушателей событий для метки
				            YMaps.Events.observe(placemark, placemark.Events.DragStart, function (obj) {
				                prev = obj.getGeoPoint().copy();
				            });
				
				            YMaps.Events.observe(placemark, placemark.Events.Drag, function (obj) {
				                var current = obj.getGeoPoint().copy();
				
				                // Увеличиваем пройденное расстояние
				                distance += current.distance(prev);
				                prev = current;				
				                obj.setIconContent("Пробег: " + YMaps.humanDistance(distance));
				                
				            });
				
				            YMaps.Events.observe(placemark, placemark.Events.DragEnd1, function (obj) {
				                // Задаем контент для балуна
				                placemark.name = "Результат";
				                placemark.description = "Координаты: " + placemark.getGeoPoint();
				                placemark.openBalloon();
				
				                // Стираем содержимое метки и обнуляем расстояние
				                obj.setIconContent(null);
				                distance = 0;
				
				                obj.update();
				            });
		                   
                           /*
		                   var options = {draggable: true, hideIcon:true,hasBalloon: true, hintOptions: {maxWidth: 100,offset: new YMaps.Point(5, 5)}};		        	
				           var placemark = new YMaps.Placemark(geocoder.get(0).getGeoPoint(), options);
				           placemark.name        = "' . $name . '";
				           placemark.description = "' . $adress . '";
		                   map.addOverlay(placemark); 	
		                   */                   
		                }
		            });
		            
		        	/*// Установка для карты ее центра и масштаба
		        	//map.setCenter(new YMaps.GeoPoint(' . $cx . ', ' . $cy . '), ' . $ch . ');		        
		        
                    // подключаем переключатель типов карт, тулбар, масштабной линейки, элемент масштабирования
		        	map.addControl(new YMaps.ToolBar());
		        	map.addControl(new YMaps.ScaleLine(), new YMaps.ControlPosition(YMaps.ControlPosition.TOP_RIGHT,new YMaps.Point(5, 10)));
		       	    map.addControl(new YMaps.SmallZoom());             
		        	// map.addControl(new YMaps.TypeControl()); 		       		
		        	
		        	// Создаем метку и добавляем ее на карту
		        	var options = {draggable: true, hideIcon:true,hasBalloon: true, hintOptions: {maxWidth: 100,offset: new YMaps.Point(5, 5)}};		        	
		        	var placemark = new YMaps.Placemark(new YMaps.GeoPoint(' . $cx . ', ' . $cy . '), options);
		        	placemark.name        = "' . $name . '";
		        	placemark.description = "' . $adress . '";
                    map.addOverlay(placemark);
                    
                    // Открыаем балун
                    //placemark.openBalloon();  */         
		        
		     })
		');

		echo '<div id="YMapsID" class ="span-16" style="height:300px"></div>';
 
    }
}