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

namespace Bricks\Plugin;

use Zend\Loader\SplAutoloader;

class ClassMapAutoloader implements SplAutoloader {	

	/**
	 * @var array
	 */
	protected static $classMap = array();
	
	/**
	 * @var string
	 */
	protected static $cachedir;
	
	/**
	 * @param string $cachedir
	 */
	public function __construct($options=null){
		$this->setOptions($options);		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Zend\Loader\SplAutoloader::setOptions()
	 */
	public function setOptions($options){
		if(isset($options['cachedir'])){
			static::$cachedir = $options['cachedir'];
		}		
		$file = static::$cachedir.'/autoloadClassmap.php';
		if(file_exists($file)){
			$this->setClassMapArray(require($file));
		}
	}
	
	/**
	 * @param array $map
	 */
	public function setClassMapArray(array $map=array()){
		static::$classMap = $map;
	}
	
	/**
	 * @param string $class
	 * @param string $path
	 */
	public function setClassMap($class,$path){
		static::$classMap[$class] = $path;
	}
	
	/**
	 * @param string $class
	 * @return string
	 */
	public function getClassMap($class){
		if($this->hasClassMap($class)){
			return static::$classMap[$class];
		}
	}
	
	/**
	 * @param string $class
	 * @return boolean
	 */
	public function hasClassMap($class){
		return isset(static::$classMap[$class]);
	}
	
	/**
	 * @return array
	 */
	public function getClassMapArray(){
		return static::$classMap;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Zend\Loader\SplAutoloader::register()
	 */
	public function register(){		
		spl_autoload_register(array($this,'autoload'),true,true);
	}
	
	/**
	 * @param string $class
	 * @return boolean
	 */
	public function autoload($class){
		if($this->hasClassMap($class)){			
			$src = $this->getClassMap($class);
			require_once($src);
			return true;		
		}		
		return false;
	}
	
}