<?php

$cfg = require(__DIR__.'/config/module.config.php');
if(!isset($cfg['BricksClassLoader']['BricksPlugin']['BricksPlugin'])){
	return;
}
if(!isset($cfg['BricksConfig']['BricksPlugin'])){
	return;
}

$cl = $cfg['BricksClassLoader']['BricksPlugin']['BricksPlugin'];
$cfg = $cfg['BricksConfig']['BricksPlugin']['BricksPlugin'];

$cachedir = rtrim($cfg['cachedir'],'/');
$eventManager = $cfg['composerFile']['eventManager'];

$filepath = $cachedir.'/autoloadClassmap.php';
if(!is_dir(dirname($filepath))){
	mkdir($filepath,0750,true);
}
if(!file_exists($filepath)){
	file_put_contents($filepath,'<?php return array(); ?>');
}

require_once(__DIR__.'/src/Bricks/Plugin/Extender/VisitorInterface.php');
require_once(__DIR__.'/src/Bricks/Plugin/ClassMapAutoloader.php');

if(false!==$cfg['composerFile']['enabled']){
	$loader = new Bricks\Plugin\ClassMapAutoloader(array(
		'cachedir' => $cachedir
	));
	$loader->register();
	
	foreach($cfg['composerFile']['requireOnce'] AS $file){
		require_once($file);
	}
	/**
	 * @var \Zend\EventManager\EventManager
	 */
	$eventManager = new $eventManager();
	foreach($cfg['listeners'] AS $event => $callbacks){
		foreach($callbacks AS $callback => $priority){			
			$eventManager->attach($event,function($e) use($callback){
				$parts = explode('::',$callback);
				$obj = new $parts[0];
				return $obj->$parts[1]($e);
			},$priority);			
		}
	}
	
	$GLOBALS['BricksPlugin/EventManager'] = $eventManager;
	
}