<?php

/**
 * Bricks Framework & Bricks CMS
 * http://bricks-cms.org
 *
 * The MIT License (MIT)
 * Copyright (c) 2015 bricks-cms.org
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

$cfg = require (__DIR__ . '/config/module.config.php');
if(!isset($cfg['BricksConfig']['BricksPlugin']['BricksPlugin'])) {
	return;
}
$cfg = $cfg['BricksConfig']['BricksPlugin']['BricksPlugin'];

$cachedir = rtrim($cfg['cachedir'], '/');
$eventManager = $cfg['composerFile']['eventManager'];

$filepath = $cachedir . '/autoloadClassmap.php';
if(!is_dir(dirname($filepath))) {
	mkdir($filepath, 0750, true);
}
if(!file_exists($filepath)) {
	file_put_contents($filepath, '<?php return array(); ?>');
}

require_once (__DIR__ . '/src/Bricks/Plugin/Extender/VisitorInterface.php');
require_once (__DIR__ . '/src/Bricks/Plugin/ClassMapAutoloader.php');

if(false !== $cfg['composerFile']['enabled']) {
	$loader = new Bricks\Plugin\ClassMapAutoloader(array(
		'cachedir' => $cachedir
	));
	$loader->register();
	
	foreach($cfg['composerFile']['requireOnce'] as $file) {
		require_once ($file);
	}
	
	/**
	 *
	 * @var \Zend\EventManager\EventManager
	 */
	$eventManager = new $eventManager();
	foreach($cfg['listeners'] as $event => $callbacks) {
		foreach($callbacks as $callback => $priority) {
			$eventManager->attach($event, function ($e) use($callback) {
				$parts = explode('::', $callback);
				$obj = new $parts[0]();
				return $obj->$parts[1]($e);
			}, $priority);
		}
	}
	
	$GLOBALS['BricksPlugin/EventManager'] = $eventManager;
}