<?php
/**
 * Bricks Framework & Bricks CMS
 * http://bricks-cms.org
 *
 * @link https://github.com/bricks81/BricksPlugin
 * @license http://www.gnu.org/licenses/ (GPLv3)
 */
namespace BricksPlugin\Plugin\Zend\Loader;

use Zend\EventManager\Event;
use Bricks\Plugin\Extender;
use Bricks\Plugin\Extender\VisitorInterface;
use Bricks\Plugin\ClassMapAutoloader AS MainClassLoader;

class ClassMapAutoloader implements VisitorInterface {
	
	protected $autoloader;
		
	public function extend(Extender $extender){
		$extender->eventize('Zend\Loader','ClassMapAutoloader','register');
	}
	
	public function preRegister(Event $event){		
		foreach(spl_autoload_functions() AS $autoloader){
			if(is_array($autoloader) && $autoloader[0] instanceof MainClassLoader){
				$this->autoloader = $autoloader;
				spl_autoload_unregister($autoloader);
				break;
			}
		}		
	}
	
	public function postRegister(Event $event){
		if($this->autoloader){
			spl_autoload_register($this->autoloader,true,true);
		}
	}
	
}