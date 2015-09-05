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

use Bricks\ClassLoader\ClassLoaderInterface;
use Bricks\Plugin\StorageAdapter\AdapterInterface;
use Bricks\Config\ConfigInterface;
use Zend\Config\Config;
use Bricks\Asset\StorageAdapter\StorageAdapterInterface;
use Zend\Config\Writer\PhpArray;

class Plugin {
	
	/**
	 * @var ClassLoaderInterface
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
	 * @param ConfigInterface $config
	 * @param ClassLoaderInterface $classLoader
	 * @param array $loadedModules
	 */
	public function __construct(
		ConfigInterface $config,				
		ClassLoaderInterface $classLoader,		
		array $loadedModules=array()
	){
		$this->setConfig($config);
		$this->setClassLoader($classLoader);				
		$this->setLoadedModules($loadedModules);
		
		$em = $classLoader->getClassLoader()->getServiceLocator()->get('EventManager');
		$listeners = array();
		foreach($loadedModules AS $namespace){
			$ret = $config->get('listeners',$namespace);
			if(!is_array($ret)){
				continue;
			}
			foreach($ret AS $event => $_listeners){
				foreach($_listeners AS $listener => $prio){
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
		
		$events = $em->getEvents();
		foreach($listeners AS $event => $_listeners){
			asort($_listeners);
			foreach($_listeners AS $callback => $prio){
				if(false === array_search($event,$events)){
					$em->attach($event, function ($e) use($callback) {
						$parts = explode('::', $callback);
						$obj = new $parts[0]();
						return $obj->$parts[1]($e);
					}, $prio);
				}
			}
		}
		
	}
	
	/**
	 * @param ClassLoaderInterface $classLoader
	 */
	public function setClassLoader(ClassLoaderInterface $classLoader){
		$this->classLoader = $classLoader;
	}
	
	/**
	 * @return \Bricks\ClassLoader\ClassLoaderInterface
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
			$this->storageAdapter = $this->getClassLoader()->newInstance(
				__CLASS__,__FUNCTION__,'storageAdapter','BricksPlugin',array(
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
			$this->modules[$moduleName] = $this->getClassLoader()->newInstance(
				__CLASS__,__FUNCTION__,'moduleClass',$moduleName,array(
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

/*
use Zend\Config\Config;
use Zend\Config\Writer\PhpArray;
use Bricks\Plugin\EventManager;
use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Bricks\Plugin\PluginAdapter\FileAdapter;
use Bricks\Plugin\PluginAdapter\AdapterInterface;
use PhpParser\Node;
use Bricks\Plugin\ClassLoader\ClassLoader;
use Zend\ServiceManager\ServiceLocatorInterface;

class PluginService {
	
	const SERVICE_NAME = 'Bricks.Plugin.Service';
	
	protected $eventManager;
	
	protected $config;

	public function __construct(Confi)
	
	public function init(ServiceLocatorInterface $sl){
		$cfg = $sl->get('Config');
		if(!isset($cfg['Bricks']['Plugin'])){
			throw new ConfigException('please configure Bricks.Plugin');
		}
		if(!isset($cfg['Bricks']['Plugin']['ClassLoader']['configClass'])){
			throw new ConfigException('please configure Bricks.Plugin.ClassLoader.configClass');
		}
		$class = $cfg['Bricks']['Plugin']['ClassLoader']['configClass'];
		$class::validateConfig($cfg['Bricks']['Plugin']);
		$config = new $class($cfg['Bricks']['Plugin'],true);
		
		
		if(!isset($cfg['Bricks']['Plugin']['ClassLoader']['classLoaderClass'])){
			throw new ConfigException('please configure Bricks.Plugin.ClassLoader.classLoaderClass');
		}
		$class = $config->ClassLoader->classLoaderClass;
		$classLoader = $class::getInstance($sl);
		$config->configureClassLoader($classLoader);
		if(isset($config->ClassLoader->moduleSpecific)){
			foreach($config->ClassLoader->moduleSpecifc AS $module => $_cfg){
				if(isset($_cfg->classLoaderClass)){
					$class = $_cfg->classLoaderClass;
					$obj = $class::getInstance($sl);
					$config->configureClassLoader($obj);
				}
			}
		}
		
		$class = $classLoader->getPluginServiceClass();
		$service = $classLoader->get($class);
		$config->configurePluginService($service);
		return $service;
		$this->config = $config;
		$this->setEventManager(EventManager::getInstance($this->config->listeners->toArray()));		
	}
	
	public function setEventManager(EventManager $eventManager){
		$this->eventManager = $eventManager;
	}
	
	public function getEventManager(){
		return $this->eventManager;
	}
	
	public function getPluginAdapter($module=null){
		$classLoader = ClassLoader::getInstance();
		$class = $classLoader->getPluginAdapterClass($module);
		return $classLoader->get($class);
	}
	
	public function getClassModFileName(){
		return $this->classModFileName;		
	}
	
	public function setClassModFileName($filename){
		$this->classModFileName = $filename;
	}
	
	public function getClassMod(){
		if(null==$this->classMod){
			$filename = $this->getCacheDir().'/'.$this->getClassModFileName();
			if($this->getPluginAdapter()->fileExists($filename)){
				$this->classMod = new Config($this->getPluginAdapter()->loadConfig($filename)->toArray(),true);
			} else {
				$this->classMod = new Config(array(),true);
			}			
		}
		return $this->classMod;
	}
	
	public function setClassMod($classMod){
		$this->classMod = $classMod;
	}
	
	public function getAutoloadMapFileName(){
		return $this->autoloadMapFileName;
	}
	
	public function setAutoloadMapFileName($filename){
		$this->autoloadMapFileName = $filename;
	}
	
	public function getAutoloadMap(){
		if(null==$this->autoloadMap){
			$filename = $this->getCacheDir().'/'.$this->getAutoloadMapFileName();
			if($this->getPluginAdapter()->fileExists($filename)){
				$this->autoloadMap = new Config($this->getPluginAdapter()->fileGetContents($filename)->toArray(),true);
			} else {
				$this->autoloadMap = new Config(array(),true);
			}			
		}
		return $this->autoloadMap;
	}
	
	public function getConfigWriterClass(){
		if(null==$this->configWriterClass){
			$this->configWriterClass = 'Zend\Config\Writer\PhpArray';
		}
		return $this->configWriterClass;
	}
	
	public function setConfigWriterClass($class){
		$this->configWriterClass = $class;
	}
	
	public function getConfigWriter(){
		if(null==$this->configWriter){
			$class = $this->getConfigWriterClass();
			$this->configWriter = new $class();
		}
	}
	
	public function autoCompile(){
		if($this->config->autoCompile){
						
			$mod = $this->getClassMod();
			$autoload = $this->getAutoloadMap();
						
			$writer = new PhpArray();
			
			foreach($this->config->extend->toArray() AS $class => $extends){
				
				$compile = false;
				
				if(isset($mod[$class])){
					$_time = $this->getPluginAdapter()->fileMTime($mod[$class]['filepath']);
					if($mod[$class]['time']!=$_time){
						$compile = true;
					}
					foreach($extends AS $extend){
						if(!isset($mod[$class]['extends'][$extend])){
							$compile = true;
							break;
						}
						$_time = $this->getPluginAdapter()->fileMTime($mod[$class]['extends'][$extend]['filepath']);
						if($mod[$class]['extends'][$extend]['time']!=$_time){
							$compile = true;
							break;
						}
					}
				}				
				if(!$compile){
					continue;
				}
				
				if(isset($mod[$class])){
					$classpath = $mod[$class]['filepath'];
				} else {				
					$reflection = new \ReflectionClass($class);				
					$classpath = $reflection->getFileName();
					$content = $this->getPluginAdapter()->fileGetContents($classpath);
					if(preg_match('/@referenced-file:\((.*?)\)/u',$content,$match)){
						// the autoload map has been changed to use the extended class
						$classpath = $match[1];
					}
				}
				$mod[$class]['filepath'] = $classpath;
				$mod[$class]['time'] = $this->getPluginAdapter()->fileMTime($classpath);

				foreach($extends AS $extend){
					if(isset($mod[$class]['extends'][$extend])){
						$path = $mod[$class]['extends'][$extend]['filepath'];
					} else {
						$ref = new \ReflectionClass($extend);
						$path = $ref->getFileName();
						$content = $this->getPluginAdapter()->fileGetContents($path);
						if(preg_match('/@referenced-file:\((.*?)\)/u',$content,$match)){
							// the autoload map has been changed to use the extended class
							$path = $match[1];
						}
					}
					$mod[$class]['extends'][$extend]['filepath'] = $path;
					$mod[$class]['extends'][$extend]['time'] = filemtime($path);
				}
				
				if(!$this->getPluginAdapter()->realpath($this->config->basedir)){
					throw new ConfigException('basedir is not a directory');
				}
				
				$filepath = $this->config->cachedir.'/classes/'.str_replace(
					$this->getPluginAdapter()->realpath($this->config->basedir),'',$classpath
				);
				
				// write the class
				$content = $this->_build($classpath,$extends);
				$this->getPluginAdapter()->filePutContents($filepath,$content);
				
				$autload[$class] = $filepath;
				
			}
			
			$autoload = new Config($autoload);
			$this->getPluginAdapter()->writeConfig($autoloadFile,$autoload,$writer);

			$mod = new Config($mod);
			$this->getPluginAdapter()->writeConfig($modFile,$mod,$writer);			
		}
	}
	
	public function compile($class,$extends){	
		
	}
	
	public function _parse($classpath){
		$code = $this->getPluginAdapter()->fileGetContents($classpath);
		$lexer = new Lexer();
		$parser = new Parser($lexer);
		$nodes = $parser->parse($code);
		return $nodes;
	}
	
	public function _extend($nodes,$classpath,array $extends){
		$extender = new Extender($nodes,$classpath);
		foreach($extends AS $className){
			$object = new $className();
			$object->extend($extender);
		}
	}
	
	public function _compile($nodes){
		$printer = new Standard();
		return $printer->prettyPrintFile($nodes);
	}
	
	public function _build($classpath,array $extends){
		$nodes = $this->parse($classpath);
		$this->extend($nodes,$classpath,$extends);
		return $this->compile($nodes);						
	}
	
}
*/