<?php
/**
 * Bricks Framework & Bricks CMS
 * http://bricks-cms.org
 *
 * @link https://github.com/bricks81/BricksPlugin
 * @license http://www.gnu.org/licenses/ (GPLv3)
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
