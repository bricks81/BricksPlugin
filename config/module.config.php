<?php

	return array(
		'service_manager' => array(
			'factories' => array(
				'BricksPlugin' => 'Bricks\Plugin\ServiceManager\PluginFactory',
			),
		),
		'BricksClassLoader' => array(
			'BricksClassLoader' => array(
				'BricksClassLoader' => array(
					'classLoaderClass' => 'Bricks\ClassLoader\ClassLoader',
					'defaultClassLoaderClass' => 'Bricks\ClassLoader\DefaultClassLoader',
					'defaultInstantiator' => 'Bricks\ClassLoader\DefaultInstantiator',
					'defaultFactory' => 'Bricks\ClassLoader\DefaultFactory',
				),
			),			
			'BricksPlugin' => array(
				'BricksPlugin' => array(
					'pluginClass' => 'Bricks\Plugin\Plugin',
					'moduleClass' => 'Bricks\Plugin\Module',
					'storageAdapter' => 'Bricks\Plugin\StorageAdapter\FileystemAdapter',
				),				
			),				
		),	
		
		'BricksConfig' => array(
			'BricksPlugin' => array(
				'BricksPlugin' => array(
					'storageAdapter' => array(
						'basedir' => './',
						'cachedir' => dirname(__DIR__).'/cache',
						'classModFileName' => 'classmod.php',
						'autoloadMapFileName' => 'autoloadClassmap.php',
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
					'composerFile' => array(
						'appCfgFile' => dirname(__DIR__).'/../../config/application.config.php',
						'eventManager' => 'Zend\EventManager\EventManager',
						'requireOnce' => array(
							dirname(__DIR__).'/../../vendor/zendframework/zendframework/library/Zend/Stdlib/CallbackHandler.php',
							dirname(__DIR__).'/../../vendor/zendframework/zendframework/library/Zend/Stdlib/PriorityQueue.php',
							dirname(__DIR__).'/../../vendor/zendframework/zendframework/library/Zend/EventManager/EventManagerInterface.php',
							dirname(__DIR__).'/../../vendor/zendframework/zendframework/library/Zend/EventManager/EventManager.php',
							dirname(__DIR__).'/src/BricksPlugin/Plugin/Zend/Loader/ClassMapAutoloader.php',
						),
					),
				),
			),			
		),
	);
?>