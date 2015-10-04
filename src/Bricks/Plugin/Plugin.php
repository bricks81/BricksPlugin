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

use Bricks\ClassLoader\ClassLoader;
use Bricks\Plugin\StorageAdapter\AdapterInterface;
use Bricks\Config\ConfigInterface;
use Zend\Config\Config;
use Bricks\Asset\StorageAdapter\StorageAdapterInterface;
use Zend\Config\Writer\PhpArray;

class Plugin {
	
	/**
	 * @var ClassLoader
	 */
	protected $classLoader;
		
	/**
	 * @var ConfigInterface
	 */
	protected $config;
	
	/**
	 * @var array
	 */
	protected $loadedModules = array();
	
	/**
	 * @var array
	 */
	protected $modules = array();
	
	/**
	 * @var StorageAdapterInterface
	 */
	protected $storageAdapter;
	
	/**
	 * @var \Zend\Config\Config
	 */
	protected $classMod;
	
	/**
	 * @var \Zend\Config\Config
	 */
	protected $autoloadMap;
	
	/**
	 * @param ClassLoaderInterface $classLoader
	 * @param array $loadedModules
	 */
	public function __construct(
		ClassLoader $classLoader,		
		array $loadedModules=array()
	){
		$this->setClassLoader($classLoader);
		$this->setConfig($classLoader->getConfig('BricksPlugin'));
		$this->setLoadedModules($loadedModules);
		
		$em = $classLoader->getServiceLocator()->get('EventManager');
		
		$listeners = array();
		foreach($loadedModules AS $namespace){
			$ret = $this->getConfig()->get('listeners',$namespace);
			if(!is_array($ret)){
				continue;
			}
			foreach($ret AS $event => $_listeners){
				foreach($_listeners AS $listener => $prio){
					if('BricksPlugin\Plugin\Zend\Loader\ClassMapAutoloader::preRegister'==$listener){
						continue;
					}
					if('BricksPlugin\Plugin\Zend\Loader\ClassMapAutoloader::postRegister'==$listener){
						continue;
					}
					if(!isset($listeners[$event])){
						$listeners[$event] = $_listeners;
						break;
					}
					if(false !== array_search(array($listener=>$prio),$listeners[$event])){
						$listeners[$event][] = array($listener=>$prio);	
					}					
				}
			}			
		}	
		
		foreach($listeners AS $event => $_listeners){		
			asort($_listeners);
			foreach($_listeners AS $callback => $prio){
				$em->attach($event, function ($e) use($callback,$classLoader) {					
					$parts = explode('::', $callback);
					$obj = new $parts[0]();
					$obj->setClassLoader($classLoader->getClassLoader());
					return $obj->$parts[1]($e);
				}, $prio);
			}
		}
		$GLOBALS['BricksPlugin/EventManager'] = $em;		
		
	}
	
	/**
	 * @param ClassLoader $classLoader
	 */
	public function setClassLoader(ClassLoader $classLoader){
		$this->classLoader = $classLoader;
	}
	
	/**
	 * @return \Bricks\ClassLoader\ClassLoader
	 */
	public function getClassLoader(){
		return $this->classLoader;
	}
	
	/**
	 * @param ConfigInterface $config
	 */
	public function setConfig(ConfigInterface $config){
		$this->config = $config;
	}
	
	/**
	 * @return \Bricks\Config\ConfigInterface
	 */
	public function getConfig(){
		return $this->config;
	}
	
	/**
	 * @param array $loadedModules
	 */
	public function setLoadedModules(array $loadedModules){
		$this->loadedModules = $loadedModules;
	}
	
	/**
	 * @return array
	 */
	public function getLoadedModules(){
		return $this->loadedModules;
	}
	
	/**
	 * @return StorageAdapterInterface
	 */
	public function getStorageAdapter(){
		if(!$this->storageAdapter){
			$this->storageAdapter = $this->getClassLoader()->get(
				'BricksPlugin.storageAdapter','BricksPlugin',array(
					'BricksPlugin' => $this
				)
			);
		}
		return $this->storageAdapter;
	}
	
	/**
	 * @return string
	 */
	public function getBasedir(){
		return $this->getConfig()->get('basedir');
	}
	
	/**
	 * @return string
	 */
	public function getCachedir(){
		return $this->getConfig()->get('cachedir');
	}
	
	/**
	 * @return string
	 */
	public function getClassModFilename(){
		return $this->getConfig()->get('classModFilename');
	}
	
	/**
	 * @return Config
	 * @throws \RuntimeException
	 */
	public function getClassMod(){
		if(!$this->classMod){	
			$adapter = $this->getStorageAdapter();
			$cachedir = $this->getCachedir();
			$file = $cachedir.'/'.$this->getClassModFilename();
			if(!$adapter->fileExists($file)){
				$this->classMod = new Config(array(),true);
			} else {
				$this->classMod = $adapter->loadConfig($file);
			}
		}
		return $this->classMod;
	}
	
	/**
	 * @return string
	 */
	public function getAutoloadMapFilename(){
		return $this->getConfig()->get('autoloadMapFilename');
	}
	
	/**
	 * @return Config
	 * @throws \RuntimeException
	 */
	public function getAutoloadMap(){
		if(!$this->autoloadMap){
			$adapter = $this->getStorageAdapter();
			$cachedir = $this->getCachedir();
			$file = $cachedir.'/'.$this->getAutoloadMapFilename();
			if(!$adapter->fileExists($file)){
				$this->autoloadMap = new Config(array(),true);
			} else {
				$this->autoloadMap = $adapter->loadConfig($file);
			}
		}
		return $this->autoloadMap;
	}
	
	/**
	 * @param string $moduleName
	 * @param Module $module
	 */
	public function addModule($moduleName,Module $module){
		$this->modules[$moduleName] = $module;
	}
	
	/**
	 * @param string $moduleName
	 */
	public function removeModule($moduleName){
		if(isset($this->modules[$moduleName])){
			unset($this->modules[$moduleName]);
		}
	}
	
	/**
	 * @param string $moduleName
	 * @return Module
	 */
	public function getModule($moduleName){
		if(!isset($this->modules[$moduleName])){
			$this->modules[$moduleName] = $this->getClassLoader()->get(
				'BricksPlugin.moduleClass',$moduleName,array(
					'BricksPlugin' => $this,
					'moduleName' => $moduleName
				)				
			);
		}
		return $this->modules[$moduleName];
	}
	
	/**
	 * @param array $modules
	 */
	public function autoCompile(array $modules=null){
		$modules = null!==$modules?$modules:$this->getLoadedModules();
		foreach($modules AS $module){
			if($this->getConfig()->get('autoCompile',$module)){				
				$module = $this->getModule($module);
				$module->compile();
			}
		}
	}
	
	public function writeAutoloadMap(){
		$cachedir = $this->getCachedir();
		$file = $cachedir.'/'.$this->getAutoloadMapFilename();
		$writer = new PhpArray();
		$writer->toFile($file,$this->getAutoloadMap(),true);
	}
	
	public function writeClassMod(){
		$cachedir = $this->getCachedir();
		$file = $cachedir.'/'.$this->getClassModFilename();
		$writer = new PhpArray();
		$writer->toFile($file,$this->getClassMod(),true);
	}	
	
}