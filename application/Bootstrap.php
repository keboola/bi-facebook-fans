<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	protected function _initBase()
	{
		setlocale(LC_ALL, 'en_US.UTF8');
		ini_set("url_rewriter.tags","");
		Zend_Session::start();
	}

	protected function _initAutoload()
	{
		$autoloader = Zend_Loader_Autoloader::getInstance();
		$autoloader->registerNamespace('App_');
		$autoloader->registerNamespace('Ladybug');

		$resourceLoader = new Zend_Loader_Autoloader_Resource(array(
		    'basePath'      => APPLICATION_PATH,
		    'namespace'     => '',
		    'resourceTypes' => array(
		        'model' => array(
		            'path'      => 'models/',
					'namespace' => 'Model_',
				),
				'form' => array(
		            'path'      => 'forms/',
					'namespace' => 'Form_',
				)
			),
		));

		return $resourceLoader;
	}

	protected function _initConfig()
	{
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/config.ini', APPLICATION_ENV, Array('allowModifications' => true));		
		Zend_Registry::set('config', $config);
		return $config;
	}

	protected function _initNDebug()
	{
		$config = $this->bootstrap('config')->getResource('config');		

		if (!defined('APPLICATION_ENV')) {
			define('APPLICATION_ENV', $config->app->env);
		}		

		if (APPLICATION_ENV == 'development') {
			ini_set('display_startup_errors', 1);
			ini_set('display_errors', 1);
			
			Ladybug\Loader::loadHelpers();
		}

		if (isset($_SERVER['HOSTNAME']))
			$_SERVER['SERVER_NAME'] = $_SERVER['HOSTNAME'];		

		require_once 'Nette/NDebugger.php';
		NDebugger::enable();
		NDebugger::$logDirectory = ROOT_PATH.'/logs';
		NDebugger::$email = $config->app->admin;
		NDebugger::$productionMode = (APPLICATION_ENV == 'production');
		NLogger::$emailSnooze = 3600;		
	}

	protected function _initDb()
	{
		$config = $this->bootstrap('config')->getResource('config');

		// connect do db
		$db = Zend_Db::factory('pdo_mysql', array(
			'host'		=> $config->db->host,
			'username'	=> $config->db->login,
			'password'	=> $config->db->password,
			'dbname'	=> $config->db->db
		));

		$db->getConnection();
		$db->query('SET NAMES utf8');

		Zend_Db_Table::setDefaultAdapter($db);
		Zend_Registry::set('db', $db);
		return $db;
	}

	protected function _initCache()
	{
		$permanentCache = Zend_Cache::factory(
			'Core',
			'File',
			array(
				'lifetime' => 60 * 60 * 24 * 31, // 1 month
				'automatic_serialization' => true
			),
			array('cache_dir' => ROOT_PATH . '/cache/')
		);

		$manager = new Zend_Cache_Manager();
		$manager->setCache('permanent', $permanentCache);

		Zend_Registry::set('cache', $manager);
	}

	protected function _initLocale()
	{
		date_default_timezone_set('Europe/Prague');
		$localeFull = 'en_US';
		$localeShort = 'en';

		if(!empty($_REQUEST['locale'])) {
			$_pos = strpos($_REQUEST['locale'], '_');
			if($_pos !== FALSE) {
				$loc = substr($_REQUEST['locale'], 0, strpos($_REQUEST['locale'], '_'));
				if(file_exists(APPLICATION_PATH . '/languages' . $loc)) {
					$localeFull = $_REQUEST['locale'];
					$localeShort = $loc;
				}
			}
		}

		setlocale(LC_ALL, $localeFull.'.UTF8');

		Zend_Registry::set('locale', $localeShort);
		Zend_Registry::set('Zend_Locale', new Zend_Locale($localeFull));

		$cache = Zend_Registry::get('cache');
		if ($cache)
			Zend_Locale::setCache($cache->getCache('permanent'));
	}

	/**
	public function _initTranslatorSettings()
	{
		$registry = Zend_Registry::getInstance();

		if (APPLICATION_ENV == 'production') {
			$cache = Zend_Registry::get('cache');
			if ($cache)
				Zend_Translate::setCache($cache->getCache('permanent'));
		}

		$translate = new App_Translate(
			'csv',
			APPLICATION_PATH.'/languages/'.$registry->locale.'/translations.csv',
			$registry->locale
		);
		Zend_Form::setDefaultTranslator($translate);

		$registry->Zend_Translate = $translate;
		return $registry->Zend_Translate;
	}
	 * 
	 */

	protected function _initViewSettings()
	{
		$this->bootstrap('view');
		$view = $this->getResource('view');

		$view->doctype('HTML5');

		$view->setBasePath(APPLICATION_PATH . '/views/');
		$view->addScriptPath(APPLICATION_PATH . '/views/scripts');
		$view->addScriptPath(APPLICATION_PATH . '/views/');
		$view->addScriptPath(APPLICATION_PATH . '/layouts/');

		$view->setHelperPath(APPLICATION_PATH . '/views/helpers', 'App_View_Helper');
		$view->addHelperPath('ZendX/JQuery/View/Helper', 'ZendX_JQuery_View_Helper');

		$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
		$viewRenderer->setView($view);
		Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

		Zend_Layout::startMvc();
		ZendX_JQuery::enableView($view);
	}
	
}

