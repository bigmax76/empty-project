<?php
// Памятка: для того чтобы производить вывод в консоль на русском (utf-8)
// необходимо установить для консоли шрифт Lucida Console (правая кнопка мыши на title консоли ) 
// выполнить в консоли команду: chcp 65001

// инициализация cli
require_once 'zf-cli.php';

// подключаем ресурсы необходимые для работы скрипта
//$application->bootstrap(array('autoload','frontController','db',));
$application->bootstrap();
Model_Cron_Service::getBalterioImages();