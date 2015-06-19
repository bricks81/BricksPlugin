<?php

$cfg = require(__DIR__.'/config/module.config.php');
if(!isset($cfg['BricksClassLoader']['BricksPlugin']['BricksPlugin'])){
	return;
}
if(!isset($cfg['BricksPlugin'])){
	return;
}

$cl = $cfg['BricksClassLoader']['BricksPlugin']['BricksPlugin'];
$cfg = $cfg['BricksConfig']['BricksPlugin']['BricksPlugin'];

$cachedir = rtrim($cfg['storageAdapter']['cachedir'],'/');
$appCfgFile = $cfg['composerFile']['appCfgFile'];
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

$app = require($appCfgFile);
if(false!==array_search('BricksPlugin',$app['modules'])){
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
			$eventManager->attach($event,$callback,$priority);
		}
	}
	
	$GLOBALS['BricksPlugin/EventManager'] = $eventManager;
	
}