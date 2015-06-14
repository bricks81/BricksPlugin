<?php
/**
 * Bricks Framework & Bricks CMS
 * http://bricks-cms.org
 *
 * @link https://github.com/bricks81/BricksPlugin
 * @license http://www.gnu.org/licenses/ (GPLv3)
 */
namespace Bricks\Plugin;

use Bricks\ClassLoader\ClassLoaderInterface;
use Bricks\Plugin\StorageAdapter\AdapterInterface;
use Bricks\Config\ConfigInterface;

class Plugin {
	
	protected $classLoader;
		
	protected $config;
	
	protected $loadedModules = array();
	
	protected $modules = array();
	
	public function __construct(
		ConfigInterface $config,
		ClassLoaderInterface $classLoader,		
		array $loadedModules=array())
	{
		$this->setConfig($config);
		$this->setClassLoader($classLoader);				
		$this->setLoadedModules($loadedModules);
	}
	
	public function setClassLoader(ClassLoaderInterface $classLoader){
		$this->classLoader = $classLoader;
	}
	
	public function getClassLoader(){
		return $this->classLoader;
	}
	
	public function setConfig(ConfigInterface $config){
		$this->config = $config;
	}
	
	public function getConfig(){
		return $this->config;
	}
	
	public function setLoadedModules(array $loadedModules){
		$this->loadedModules = $loadedModules;
	}
	
	public function getLoadedModules(){
		return $this->loadedModules;
	}
	
	public function addModule($moduleName,Module $module){
		$this->modules[$moduleName] = $module;
	}
	
	public function removeModule($moduleName){
		if(isset($this->modules[$moduleName])){
			unset($this->modules[$moduleName]);
		}
	}
	
	public function getModule($moduleName){
		if(!isset($this->modules[$moduleName])){
			$this->modules[$moduleName] = $this->getClassLoader()->newInstance(__CLASS__,__FUNCTION__,'moduleClass',$moduleName,array(
				'BricksPlugin' => $this,
				'moduleName' => $moduleName				
			));
		}
		return $this->modules[$moduleName];
	}
	
	public function autoCompile(array $modules=null){
		$modules = null!==$modules?$modules:$this->getLoadedModules();
		foreach($modules AS $module){
			if($this->getConfig()->get('autoCompile',$module)){
				$module = $this->getModule($module);
				$module->compile();
			}
		}
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