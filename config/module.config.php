<?php
return array(
	'service_manager' => array(
		'factories' => array(
			'BricksPlugin' => 'Bricks\Plugin\ServiceManager\PluginFactory'
		)
	),	
	'BricksConfig' => array(
		'BricksClassLoader' => array(
			'BricksClassLoader' => array(
				'aliasMap' => array(
					'BricksPlugin' => array(
						'pluginClass' => 'Bricks\Plugin\Plugin',
						'moduleClass' => 'Bricks\Plugin\Module',
						'storageAdapter' => 'Bricks\Plugin\StorageAdapter\FilesystemAdapter',
						'extender' => 'Bricks\Plugin\Extender'
					),
				),
			),
		),
		'BricksPlugin' => array(
			'BricksPlugin' => array(
				'basedir' => './',
				'cachedir' => dirname(__DIR__ ) . '/cache',
				'classModFilename' => 'classmod.php',
				'autoloadMapFilename' => 'autoloadClassmap.php',
				'composerFile' => array(
					'enabled' => true,
					'eventManager' => 'Zend\EventManager\EventManager',
					'requireOnce' => array(
						'./vendor/zendframework/zend-stdlib/src/CallbackHandler.php',
						'./vendor/zendframework/zend-stdlib/src/PriorityQueue.php',
						'./vendor/zendframework/zend-eventmanager/src/EventManagerInterface.php',
						'./vendor/zendframework/zend-eventmanager/src/EventManager.php',
						dirname(__DIR__ ) . '/src/BricksPlugin/Plugin/Zend/Loader/ClassMapAutoloader.php'
					)
				),
				'autoCompile' => true,
				'extend' => array(
					'Zend\Loader\ClassMapAutoloader' => array(
						'BricksPlugin\Plugin\Zend\Loader\ClassMapAutoloader'
					)
				),
				'listeners' => array(
					'Zend\Loader\ClassMapAutoloader::register.pre' => array(
						'BricksPlugin\Plugin\Zend\Loader\ClassMapAutoloader::preRegister' => -100000
					),
					'Zend\Loader\ClassMapAutoloader::register.post' => array(
						'BricksPlugin\Plugin\Zend\Loader\ClassMapAutoloader::postRegister' => -100000
					)
				)
			)
		)
	)
);
?>