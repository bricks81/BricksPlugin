<?php
/**
 * Bricks Framework & Bricks CMS
 * http://bricks-cms.org
 *
 * @link https://github.com/bricks81/BricksPlugin
 * @license http://www.gnu.org/licenses/ (GPLv3)
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