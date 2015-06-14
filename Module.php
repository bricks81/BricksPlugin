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
use Bricks\Plugin\PluginService;

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
    
    public function onBootstrap(MvcEvent $e){    	    	
    	$service = $e->getApplication()->getServiceManager()->get('BricksPlugin');
    	$service->autoCompile();
    }
    
}
