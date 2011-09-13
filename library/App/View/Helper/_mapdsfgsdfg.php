<?php
class App_View_Helper_YandexMap extends Zend_View_Helper_Abstract
{
	public function yandexMap()	 
    {
    	$this->view->headscript()->appendFile('http://api-maps.yandex.ru/1.1/index.xml?key=AGJ6XksBAAAA-m2UfAIAD-4LCvSs-W8VAwi6nybQ9rHujjUAAAAAAAAAAADeCx8wHnZi3tYz3-YeXKmQJVw5Qg==');
		$this->view->headscript()->appendFile('/public/js/ymap.js');
		$this->view->headScript()->appendScript('
			// Создает обработчик события window.onLoad
		    YMaps.jQuery(function () {	        
		        		    
		        // Создает экземпляр карты и привязывает его к созданному контейнеру
		        var map = new YMaps.Map(YMaps.jQuery("#YMapsID")[0]);		        
		        
		        // Устанавливает начальные параметры отображения карты: центр карты и коэффициент масштабирования
		        map.setCenter(new YMaps.GeoPoint(36.279065,49.980149), 10);
		        
		        // Переключатель трех стандартных типов карт (отключено)
                // var typeControl = new YMaps.TypeControl();
		        // map.addControl(typeControl);
		        
		        // тулбар ("Перемещение", "Лупа","Линейка")
		        var toolBar = new YMaps.ToolBar();
		        map.addControl(toolBar);
		        
		        // Масштабная линейка
		        var scaleLine = new YMaps.ScaleLine();
		        map.addControl(scaleLine,new YMaps.ControlPosition(YMaps.ControlPosition.TOP_RIGHT, new YMaps.Point(5, 10)));
		        
		        // Создает элемент масштабирования (малый)
                var zoomControl = new YMaps.SmallZoom();
                map.addControl(zoomControl);
                
               
		        /*  /////////////////////////////////////
		        // Создает пользовательскую кнопку
				var button = new YMaps.ToolBarButton({ 
				    caption: "Добавить метку", 
				    hint: "Добавляет метку в центр карты"
				});
				// При щелчке на кнопке добавляется новая кнопка
                YMaps.Events.observe(button, button.Events.Click, function () {
                     this.addOverlay(new YMaps.Placemark(map.getCenter(), {draggable: true}));
                }, map);
				// Добавление кнопки на панель инструментов
                toolBar.add(button);
                */
                ///////////////////////////////////////
                // Создание кнопки-флажка
	            var button = new YMaps.ToolBarToggleButton({ 
	                icon: "/public/image/icon-fullscreen.png", 
	                hint: "Разворачивает карту на весь экран"
	            });
	
	            // Если кнопка активна, то карта разворачивается во весь экран
	            YMaps.Events.observe(button, button.Events.Select, function () {
	                setSize();
	            });
	            
	            // Если кнопка неактивна, то карта принимает фиксированный размер
	            YMaps.Events.observe(button, button.Events.Deselect, function () {
	                setSize(600, 400);
	            });
	            
	            // Функция устанавливает новые размеры для карты
	            function setSize (newWidth, newHeight) {
	                YMaps.jQuery("#YMapsID").css({
	                    width: newWidth || "", 
	                    height: newHeight || ""
	                });
	                map.redraw();
	            }
	
	            // Добавление кнопки на панель инструментов
	            toolBar.add(button);
	
	            // Добавление панели инструментов на карту
	            map.addControl(toolbar);	            
            /////////////////////////////////////////////////////
	            var point = new YMaps.GeoPoint(36.27,49.98);
	
	            var placemark = new YMaps.Placemark(point);
	
	            map.addOverlay(placemark);
            
            
		        
		     })
		');

		echo '<div id="YMapsID" class ="span-16" style="height:300px"></div>';
 
    }
}