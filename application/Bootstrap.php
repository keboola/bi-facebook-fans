<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	protected function _initBase()
	{
		setlocale(LC_ALL, 'en_US.UTF8');
		ini_set("url_rewriter.tags","");
		date_default_timezone_set('Europe/Prague');
		
		$front = Zend_Controller_Front::getInstance();
		$front->setParam('noErrorHandler', true);
		
		// Setup of Nette Debug
		require_once "Nette/Utils/exceptions.php";
		require_once "Nette/Utils/shortcuts.php";
		require_once "Nette/Debug/Debug.php";
		$registry = Zend_Registry::getInstance();

		define('NETTE', TRUE);
		define('NETTE_DIR', APPLICATION_PATH.'/../library/Nette');
		define('NETTE_VERSION_ID', 907); // v0.9.7
		define('NETTE_PACKAGE', 'PHP 5.2 prefixed');
		
		//if (APPLICATION_ENV == 'development') {
			NDebug::enable();
		/*} else {
			NDebug::enable('production', APPLICATION_PATH.'/../logs/php-error.log', $registry->config->adminEmail);
		}*/
	}
	
	protected function _initAutoload()
	{
		$autoloader = Zend_Loader_Autoloader::getInstance();
		$autoloader->registerNamespace('App_');

		$resourceLoader = new Zend_Loader_Autoloader_Resource(array(
		    'basePath'      => APPLICATION_PATH,
		    'namespace'     => '',
		    'resourceTypes' => array(
		        'model' => array(
		            'path'      => 'models/',
					'namespace' => 'Model_',
				)
			),
		));

		return $resourceLoader;
	}

	protected function _initConfig()
	{
		$registry = Zend_Registry::getInstance();

		$configCommon = new Zend_Config_Ini(APPLICATION_PATH . '/configs/common.ini', 'common', Array('allowModifications' => true));
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/config.ini', 'config', Array('allowModifications' => true));
		$configMerged = $configCommon->merge($config);
		$configMerged->setReadOnly();
		$registry->config = $configMerged;
	}
	
}
