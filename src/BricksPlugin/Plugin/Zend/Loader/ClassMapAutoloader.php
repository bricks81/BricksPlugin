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
	protected static $autoloader;
		
	/**
	 * @var Extender
	 */
	protected $extender;
	
	/**
	 * @param Extender $extender
	 */
	public function __construct(Extender $extender=null){
		$this->setExtender($extender);
	}
	
	/**
	 * @param Extender $extender
	 */
	public function setExtender(Extender $extender=null){
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
				self::$autoloader = $autoloader;
				spl_autoload_unregister($autoloader);
				break;
			}
		}		
	}
	
	/**
	 * @param Event $event
	 */
	public function postRegister(Event $event){		
		if(self::$autoloader){						
			spl_autoload_register(self::$autoloader,true,true);
		}
	}
	
}