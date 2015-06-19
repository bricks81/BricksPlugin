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

/**
 * This class will hold the autoloader with it's classmap
 * in front of the internal autoloading stack.
 */
class ClassMapAutoloader implements VisitorInterface {
	
	/**
	 * @var autoloader tmp
	 */
	protected $autoloader;
		
	/**
	 * @var Extender
	 */
	protected $extender;
	
	/**
	 * @param Extender $extender
	 */
	public function __construct(Extender $extender){
		$this->setExtender($extender);
	}
	
	/**
	 * @param Extender $extender
	 */
	public function setExtender(Extender $extender){
		$this->extender = $extender;
	}
	
	/**
	 * @return \Bricks\Plugin\Extender
	 */
	public function getExtender(){
		return $this->extender;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Bricks\Plugin\Extender\VisitorInterface::extend()
	 */
	public function extend(){
		$this->getExtender()->eventize('Zend\Loader','ClassMapAutoloader','register');
	}
	
	/**
	 * @param Event $event
	 */
	public function preRegister(Event $event){		
		foreach(spl_autoload_functions() AS $autoloader){
			if(is_array($autoloader) && $autoloader[0] instanceof MainClassLoader){
				$this->autoloader = $autoloader;
				spl_autoload_unregister($autoloader);
				break;
			}
		}		
	}
	
	/**
	 * @param Event $event
	 */
	public function postRegister(Event $event){
		if($this->autoloader){
			spl_autoload_register($this->autoloader,true,true);
		}
	}
	
}