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

use Zend\Config\Config;
use Bricks\Plugin\StorageAdapter\StorageAdapterInterface;
use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\Lexer;
use PhpParser\Parser;

class Module {
	
	/**
	 * @var Plugin
	 */
	protected $plugin;
	
	/**
	 * @var string
	 */
	protected $moduleName;
	
	/**
	 * @var string
	 */
	protected $namespace;
	
	/**
	 * @var array
	 */
	protected $storageAdapters = array();
	
	/**
	 * @var array
	 */
	protected $config = array();
	
	/**
	 * @var array
	 */
	protected $autoloadMaps = array();
	
	/**
	 * @var array
	 */
	protected $classMods = array();
	
	/**
	 * @param Plugin $plugin
	 * @param string $moduleName
	 */
	public function __construct(Plugin $plugin,$moduleName,$defaultNamespace=null){
		$this->setPlugin($plugin);
		$this->setModuleName($moduleName);
		$this->setNamespace($defaultNamespace?:$moduleName);
	}
	
	/**
	 * @param Plugin $plugin
	 */
	public function setPlugin(Plugin $plugin){
		$this->plugin = $plugin;
	}
	
	/**
	 * @return \Bricks\Plugin\Plugin
	 */
	public function getPlugin(){
		return $this->plugin;
	}
	
	/**
	 * @param string $moduleName
	 */
	public function setModuleName($moduleName){
		$this->moduleName = $moduleName;
	}
	
	/**
	 * @return string
	 */
	public function getModuleName(){
		return $this->moduleName;
	}
	
	/**
	 * @param string $namespace
	 */
	public function setNamespace($namespace=null){
		$this->namespace = $namespace;
	}
	
	/**
	 * @return string
	 */
	public function getNamespace(){
		return $this->namespace;
	}
	
	/**
	 * @return \Bricks\Plugin\StorageAdapter\StorageAdapterInterface
	 */
	public function getStorageAdapter($namespace=null){
		$namespace = $namespace?:$this->getNamespace();
		if(!isset($this->storageAdapters[$namespace])){
			$this->storageAdapters[$namespace] = $this->getPlugin()->getClassLoader()->newInstance(
				__CLASS__,__FUNCTION__,'storageAdapter',$namespace,array(
					'Module' => $this,
					'namespace' => $namespace	
				)
			);			
		}
		return $this->storageAdapters[$namespace];
	}
	
	/**
	 * @param StorageAdapterInterface $adapter
	 * @param string $namespace
	 */
	public function setStorageAdapter(StorageAdapterInterface $adapter,$namespace=null){
		$namespace = $namespace?:$this->getNamespace();
		$this->storageAdapters[$namespace] = $adapter;
	}
	
	/**
	 * @param string $namespace
	 * @throws \RuntimeException
	 */
	public function compile($namespace=null){		
		$namespace = $namespace?:$this->getNamespace();
		$storage = $this->getStorageAdapter($namespace);
		$autoload = $this->getPlugin()->getAutoloadMap();
		$mod = $this->getPlugin()->getClassMod();
		$extend = $this->getPlugin()->getConfig()->get('extend',$namespace);
		$basedir = $this->getPlugin()->getBasedir();
		$cachedir = $this->getPlugin()->getCachedir();

		foreach($extend AS $class => $extends){					
			$compile = false;
			if(isset($mod->$class)){				
				$_time = $storage->fileMTime($mod->$class->filepath);
				if($mod->$class->time!=$_time){
					$compile = true;
				}
				foreach($extends AS $extend){
					if(!isset($mod->$class->extends->$extend)){
						$compile = true;
						break;
					}
					$_time = $storage->fileMTime($mod->$class->extends->$extend->filepath);
					if($mod->$class->extends->$extend->time!=$_time){
						$compile = true;
						break;
					}
				}
			} else {
				$compile = true;
			}
			if(!$compile){
				continue;
			}
		
			if(isset($mod->$class)){
				$classpath = $mod->$class->filepath;
			} else {
				$reflection = new \ReflectionClass($class);
				$classpath = $reflection->getFileName();
				$content = $storage->fileGetContents($classpath);
				if(preg_match('/@referenced-file:\((.*?)\)/u',$content,$match)){
					// the autoload map has been changed to use the extended class
					$classpath = $match[1];
				}
				$mod->$class = array();
			}
			$mod->$class->filepath = $classpath;
			$mod->$class->time = $storage->fileMTime($classpath);
		
			foreach($extends AS $extend){
				if(isset($mod->$class->extends->$extend)){
					$path = $mod->$class->extends->$extend->filepath;
				} else {
					$ref = new \ReflectionClass($extend);
					$path = $ref->getFileName();
					$content = $storage->fileGetContents($path);
					if(preg_match('/@referenced-file:\((.*?)\)/u',$content,$match)){
						// the autoload map has been changed to use the extended class
						$path = $match[1];
					}
				}
				
				if(!isset($mod->$class->extends)){
					$mod->$class->extends = array();
				}
				if(!isset($mod->$class->extends->$extend)){
					$mod->$class->extends->$extend = array();
				}
				$mod->$class->extends->$extend->filepath = $path;
				$mod->$class->extends->$extend->time = $storage->fileMTime($path);
			}
		
			if(!$storage->realpath($basedir)){
				throw new \RuntimeException('basedir '.$basedir.' is not a directory');
			}
		
			$filepath = $cachedir.'/classes/'.str_replace(
				$storage->realpath($basedir),'',$classpath
			);			
		
			// write the class
			$content = $this->build($classpath,$extends,$namespace);						
			$storage->filePutContents($filepath,$content);
		
			$autoload->$class = $filepath;
		
		}
		
		$this->getPlugin()->writeAutoloadMap();
		$this->getPlugin()->writeClassMod();
	}
	
	/**
	 * @param string $classpath
	 * @param string $namespace
	 * @return Node
	 */
	protected function parse($classpath,$namespace=null){
		$storage = $this->getStorageAdapter($namespace);
		$code = $storage->fileGetContents($classpath);
		$lexer = new Lexer();
		$parser = new Parser($lexer);
		$nodes = $parser->parse($code);
		return $nodes;
	}
	
	/**
	 * @param Node $nodes
	 * @param string $classpath
	 * @param array $extends
	 * @param string $namespace
	 */
	protected function extend($nodes,$classpath,array $extends,$namespace=null){
		$namespace = $namespace?:$this->getNamespace();
		$extender = $this->getPlugin()->getClassLoader()->newInstance(
			__CLASS__,__FUNCTION__,'extender',$namespace,array(
				'nodes' => $nodes,
				'classpath' => $classpath,
				'extends' => $extends,
				'namespace' => $namespace,
				'Module' => $this
			)
		);		
		foreach($extends AS $className){
			$object = $this->getPlugin()->getClassLoader()->newInstance(
				__CLASS__,__FUNCTION__,$className,$namespace,array(
					'Extender' => $extender,
					'Module' => $this,					
					'namespace' => $namespace
				)
			);
			$object->extend();
		}
	}
	
	/**
	 * @param Node $nodes
	 * @return string
	 */
	protected function _compile($nodes){
		$printer = new Standard();
		return $printer->prettyPrintFile($nodes);
	}
	
	/**
	 * @param string $classpath
	 * @param array $extends
	 * @param string $namespace
	 * @return string
	 */
	protected function build($classpath,array $extends,$namespace=null){
		$nodes = $this->parse($classpath,$namespace);
		$this->extend($nodes,$classpath,$extends,$namespace);
		return $this->_compile($nodes);
	}
	
}