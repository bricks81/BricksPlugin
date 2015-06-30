<?php
/**
 * Bricks Framework & Bricks CMS
 * http://bricks-cms.org
 *
 * @link https://github.com/bricks81/BricksPlugin
 * @license http://www.gnu.org/licenses/ (GPLv3)
 */
namespace Bricks\Plugin\ServiceManager;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PluginFactory implements FactoryInterface {
	
	/**
	 * (non-PHPdoc)
	 * @see \Zend\ServiceManager\FactoryInterface::createService()
	 */
	public function createService(ServiceLocatorInterface $sl){

		$config = $sl->get('BricksConfig')->getConfig('BricksPlugin');
		$classLoader = $sl->get('BricksClassLoader')->getClassLoader('BricksPlugin');
		$loadedModules = array_keys($sl->get('ModuleManager')->getLoadedModules());
		
		$service = $classLoader->newInstance(__CLASS__,__METHOD__,'pluginClass','BricksPlugin',array(
			'BricksConfig' => $config,
			'BricksClassLoader' => $classLoader,
			'loadedModules' => $loadedModules,
			'ServiceLocator' => $sl
		));
		
		return $service;
	}
	
}