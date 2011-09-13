<?php
require 'settings.php';
$cache  = new Assetic\Cache\FilesystemCache(CACHE_DIR_CSS);
$asset  = new Assetic\Asset\AssetCollection(array(
    new Assetic\Asset\FileAsset(CSS_DIR . '/yui3-css/reset-min.css'),    
    new Assetic\Asset\FileAsset(CSS_DIR . '/yui3-css/base-min.css'),    
    new Assetic\Asset\FileAsset(CSS_DIR . '/yui3-css/fonts-min.css'),    
    new Assetic\Asset\GlobAsset(CSS_DIR . '/*.css'),
));
	
$assetic = new Assetic\Asset\AssetCache($asset, $cache);

$mtime   = gmdate('D, d M Y H:i:s', $assetic->getLastModified()) . ' GMT';
if (is_not_modified($mtime)) {
	header('HTTP/1.0 304 Not Modified');
	exit;
}

header('Content-Type: text/css');
header('Last-Modified: ' . $mtime);
echo $assetic->dump();