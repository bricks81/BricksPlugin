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

namespace BricksPlugin;

use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\ModuleManager\ModuleEvent;

class Module {
	
	public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getAutoloaderConfig() {
        return array(
        	'Zend\Loader\ClassMapAutoloader' => array(
        		__DIR__ . '/autoload_classmap.php',
        	),
        );
    }
    
    public function init(ModuleManager $m){
    	
    	// register the global defined events to the current event manager
    	$events = $GLOBALS['BricksPlugin/EventManager']->getEvents();
    	foreach($events AS $event){
    		$listeners = $GLOBALS['BricksPlugin/EventManager']->getListeners($event);
    		foreach($listeners AS $listener){    			
    			$m->getEventManager()->attach($event,$listener);
    		}
    	}
    	$GLOBALS['BricksPlugin/EventManager'] = $m->getEventManager();
    	
    	$m->getEventManager()->attach('loadModules.post',function(ModuleEvent $e){
    		$sm = $e->getTarget()->getEvent()->getParam('ServiceManager');
    		$service = $sm->get('BricksPlugin');
    		$service->autoCompile();
    	});
    }
    
}
