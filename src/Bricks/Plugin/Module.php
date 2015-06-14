<?php
/**
 * Bricks Framework & Bricks CMS
 * http://bricks-cms.org
 *
 * @link https://github.com/bricks81/BricksPlugin
 * @license http://www.gnu.org/licenses/ (GPLv3)
 */
namespace Bricks\Plugin;

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
	 * @param Plugin $plugin
	 * @param string $moduleName
	 */
	public function __construct(Plugin $plugin,$moduleName){
		$this->setPlugin($plugin);
		$this->setModuleName($moduleName);		
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
	
	public function compile(){
		
	}
	
}