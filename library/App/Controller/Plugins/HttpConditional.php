<?php
/**
 * Плагин позволяющий осуществить кешировние на стороне клиента
 * путем ответа со статус кодом 304.
 * Страница при этом генерируется, но не отдается, если у клиента она уже есть в кеше.
 * Это позволяет экономить трафик, немного снизить нагрузку на сервер 
 * и делает сайт более SEO дружественным. (вероятно следует увеличить max-age=7200)
 * То есть поисковики смогут проиндексировать больше страниц за раз.
 * Источник: http://lobach.info/develop/zf/enable-conditional-get-in-zend-framework-app/
 * 
 * Плагин должен быть зарегестрирован последним в стеке плагинов.
 * (101 в конце)
 * $frontController->registerPlugin( new App_Controller_Plugin_HttpConditional(), 101);
 */
class App_Controller_Plugins_HttpConditional extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopShutdown()
    {
        $send_body = true;
        $etag = '"' . md5($this->getResponse()->getBody()) . '"';
        $inm = split(',', getenv("HTTP_IF_NONE_MATCH"));
        $inm = str_replace('-gzip', '', $inm);
        // TODO If the request would, without the If-None-Match header field,
        // result in anything other than a 2xx or 304 status,
        // then the If-None-Match header MUST be ignored
        foreach ($inm as $i) {
            if (trim($i) == $etag) {
                $this->getResponse()
                     ->clearAllHeaders()
                     ->setHttpResponseCode(304)
                     ->clearBody();
                $send_body = false;
                break;
            }
        }
        $this->getResponse()
             ->setHeader('Cache-Control', 'max-age=7200, must-revalidate', true)
             ->setHeader('Expires', gmdate('D, d M Y H:i:s', time() + 2 * 3600) . ' GMT', true)
             ->clearRawHeaders();
        if ($send_body) {
            $this->getResponse()
                 ->setHeader('Content-Length', strlen($this->getResponse()->getBody()));
        }
        $this->getResponse()->setHeader('ETag', $etag, true);
        $this->getResponse()->setHeader('Pragma', '');
    }
}