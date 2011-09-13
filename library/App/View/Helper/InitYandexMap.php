<?php
class App_View_Helper_InitYandexMap extends Zend_View_Helper_Abstract
{
	public function initYandexMap(Model_Shop $shop)	 
    {
    	
    	$cx = 36.229065;
    	$cy = 49.980149;
    	$ch = 10;
    	$name = 'Хата Ламината';
    	$adress = 'ул.Плехановская, 2/5';
    	//$adress = 'пр. Ленина, 52';
    	$config = Zend_Registry::get('config');
    	$yandex_maps_key = $config->yandex->maps->key;
    	$this->view->headScript()->appendFile('http://api-maps.yandex.ru/1.1/index.xml?key=' . $yandex_maps_key);
		$this->view->headScript()->appendFile('/public/js/ymap/ymap.js');
		// если карта инициализирована - выводим данные на основе кординат записаных в бд
		if ($shop->has_map)
		{
			$this->script_without_geocoder($shop);			
		}
		else{ // иначе запускаем геокодирование и выводим данные на основ адреса
			
			$this->script_with_geocoder($shop);			
		}

		echo '<div id="YMapsID" class ="span-15" style="height:300px"></div>'; 
    }
    
    private function script_with_geocoder(Model_Shop $shop)
    {
    	$name = $shop->name;
    	$adress = $shop->address;
    	
    	$this->view->headScript()->appendScript('// Создание обработчика для события window.onLoad
		    YMaps.jQuery(function () 
		    	{
		    		// Создает экземпляр карты и привязывает его к созданному контейнеру
		            var map = new YMaps.Map(YMaps.jQuery("#YMapsID")[0]);	
		            map.addControl(new YMaps.ToolBar());
		        	map.addControl(new YMaps.ScaleLine(), new YMaps.ControlPosition(YMaps.ControlPosition.TOP_RIGHT,new YMaps.Point(5, 10)));
		       	    map.addControl(new YMaps.Zoom());						   					   		 	            

		            // Создание объекта геокодера
                    var geocoder = new YMaps.Geocoder("Харьков ' . $adress . '", {results: 1});
                    
                    // или так 
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
                           placemark.setBalloonContent("Чтобы указать это местоположение <br /> и масштаб нажмите на кнопку <br /><strong>Сохранить</strong>");
                           
                           // центрируем метку по завершении перетаскивания
                           YMaps.Events.observe(placemark, placemark.Events.DragEnd, function () {
						        map.setCenter(placemark.getGeoPoint());
						   });
						   YMaps.Events.observe(map, map.Events.Update, function () {	
						   		var form = document.forms["yandexMap"];
						        form.lat.value  = map.getCenter().getLat();  // широта геоточки
						        form.lng.value  = map.getCenter().getLng();  // долгота геоточки
						        form.zoom.value = map.getZoom()
						        
						        //form.lower_lat.value = map.getBounds().getLeftBottom().getLat(); // широта геоточки.
						        //form.lower_lng.value = map.getBounds().getLeftBottom().getLng(); // долгота геоточки.
						        //form.upper_lat.value = map.getBounds().getRightTop().getLat();   // широта геоточки.
						        //form.upper_lng.value = map.getBounds().getRightTop().getLng();   // долгота геоточки
						    });
						   
                           // Установка слушателей событий для метки		            
				           YMaps.Events.observe(placemark, placemark.Events.Drag, function (obj) {
				            	obj.setIconContent("' . $name . '");				            	 				                
				           });	                         
                                           
		                }
		            });		        	      
		        
		     })
		');
    }
    
    private function script_without_geocoder(Model_Shop $shop)
    {
    	$name = $shop->name;
    	$adress = $shop->address;
    	$map = $shop->getMap();
    	$lat  = $map->lat;
    	$lng  = $map->lng;
    	$zoom = $map->zoom;
    	echo $lat.', '.$lng.', '.$zoom;
    	$this->view->headScript()->appendScript('// Создание обработчика для события window.onLoad
		    YMaps.jQuery(function () 
		    	{
		    		// Создает экземпляр карты и привязывает его к созданному контейнеру
		            var map = new YMaps.Map(YMaps.jQuery("#YMapsID")[0]);	
		            map.addControl(new YMaps.ToolBar());
		        	map.addControl(new YMaps.ScaleLine(), new YMaps.ControlPosition(YMaps.ControlPosition.TOP_RIGHT,new YMaps.Point(5, 10)));
		       	    map.addControl(new YMaps.Zoom());						   					   		 	            

		           
		           // Установка для карты ее центра и масштаба	                  
		           // map.setBounds(geocoder.get(0).getBounds());	
		            map.setCenter(new YMaps.GeoPoint('.$lng.','.$lat.'), '.$zoom.');
		        	                   	
		                
		            // Создание перетаскиваемой метки
                    var placemark = new YMaps.Placemark(map.getCenter(), {draggable: true});
                    map.addOverlay(placemark);
                    placemark.setIconContent("' . $name . '");
                    placemark.setBalloonContent("Чтобы указать это местоположение <br /> и масштаб нажмите на кнопку <br /><strong>Сохранить</strong>");
                          
                    // центрируем метку по завершении перетаскивания
                    YMaps.Events.observe(placemark, placemark.Events.DragEnd, function () {
					    map.setCenter(placemark.getGeoPoint());
					});
					YMaps.Events.observe(map, map.Events.Update, function () {	
						var form = document.forms["yandexMap"];
					    form.lat.value  = map.getCenter().getLat();  // широта геоточки
					    form.lng.value  = map.getCenter().getLng();  // долгота геоточки
					    form.zoom.value = map.getZoom();					    
					});
						   
                    // Установка слушателей событий для метки		            
				    YMaps.Events.observe(placemark, placemark.Events.Drag, function (obj) {
				       	obj.setIconContent("' . $name . '");				            	 				                
				    });	                         
                    		            		        	      
		        
		        })
		');
    }
    
    
}