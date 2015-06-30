<?php

	use Zend\Mvc\Application;
return array(
		'service_manager' => array(
			'factories' => array(
				'BricksPlugin' => 'Bricks\Plugin\ServiceManager\PluginFactory',
			),
		),
		'BricksClassLoader' => array(
			'BricksPlugin' => array(
				'BricksPlugin' => array(
					'pluginClass' => 'Bricks\Plugin\Plugin',
					'moduleClass' => 'Bricks\Plugin\Module',
					'storageAdapter' => 'Bricks\Plugin\StorageAdapter\FilesystemAdapter',
					'extender' => 'Bricks\Plugin\Extender',
				),
			),				
		),		
		'BricksConfig' => array(
			'BricksPlugin' => array(
				'BricksPlugin' => array(					
					'basedir' => './',
					'cachedir' => dirname(__DIR__).'/cache',
					'classModFilename' => 'classmod.php',
					'autoloadMapFilename' => 'autoloadClassmap.php',
					'composerFile' => array(
						'enabled' => true,						
						'eventManager' => 'Zend\EventManager\EventManager',
						'requireOnce' => array(
							'./vendor/zendframework/zendframework/library/Zend/Stdlib/CallbackHandler.php',
							'./vendor/zendframework/zendframework/library/Zend/Stdlib/PriorityQueue.php',
							'./vendor/zendframework/zendframework/library/Zend/EventManager/EventManagerInterface.php',
							'./vendor/zendframework/zendframework/library/Zend/EventManager/EventManager.php',
							dirname(__DIR__).'/src/BricksPlugin/Plugin/Zend/Loader/ClassMapAutoloader.php',
						),
					),
					'autoCompile' => true,
					'extend' => array(
						'Zend\Loader\ClassMapAutoloader' => array(
							'BricksPlugin\Plugin\Zend\Loader\ClassMapAutoloader',
						),
					),
					'listeners' => array(
						'Zend\Loader\ClassMapAutoloader::register.pre' => array(
							'BricksPlugin\Plugin\Zend\Loader\ClassMapAutoloader::preRegister' => -100000,
						),
						'Zend\Loader\ClassMapAutoloader::register.post' => array(
							'BricksPlugin\Plugin\Zend\Loader\ClassMapAutoloader::postRegister' => -100000,
						),
					),					
				),				
			),			
		),
	);
?>