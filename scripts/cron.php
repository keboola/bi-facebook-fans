<?php
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap();

$_p = new Model_Pages();

$pages = $_p->fetchAll(array('isActive=?' => 1));
$since = date('Y-m-d', strtotime('-4 days'));
$until = date('Y-m-d');

$config = Zend_Registry::get('config');

foreach($pages as $page) {
	try {
		echo "**************\nFetching stats for: ".$page->name."\n";
		$_i = new App_Import($page);
		$result = $_i->run($since, $until);
		if ($result) {
			if (!$page->isImported) {
				$page->isImported = 1;
				$page->save();
			}

			$fgd = new App_FacebookGoodData($config, $page->findParentRow('Model_Accounts')->idGD, $page->id);
			if (!$page->isInGD) {
				$fgd->setup();
				$page->isInGD = 1;
				$page->save();
			}

			$fgd->loadData();
		}
		echo "\n\n";
	} catch(App_FacebookException $e) {
		App_Debug::send('Error for page '.$page->id. '('.$page->name.'), interval: '.$since.'-'.$until.' - '.$e->getMessage()."\n");

		echo "There was an error during talking to Facebook API. Try again please.\n";
		continue;
	}
}