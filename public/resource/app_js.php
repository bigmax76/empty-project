<?php
require 'settings.php'; 
$cache = new Assetic\Cache\FilesystemCache(CACHE_DIR_JS);
$asset = new Assetic\Asset\AssetCollection(array(
    new Assetic\Asset\FileAsset(JS_DIR . '/core/jquery-1.6.2.min.js'),    
    new Assetic\Asset\GlobAsset(JS_DIR . '/jquery.plugins/*.js'),	    
    new Assetic\Asset\GlobAsset(JS_DIR . '/php.js/*.js'),
    new Assetic\Asset\GlobAsset(JS_DIR . '/*.js'),    
));

$assetic = new Assetic\Asset\AssetCache($asset, $cache);

$mtime   = gmdate('D, d M Y H:i:s', $assetic->getLastModified()) . ' GMT';
if (is_not_modified($mtime)) {
	header('HTTP/1.0 304 Not Modified');
	exit;
}

header('Content-Type: text/javascript');
header('Last-Modified: ' . $mtime);
echo $assetic->dump();