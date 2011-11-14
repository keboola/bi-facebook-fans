<?php
define('ROOT_PATH', dirname(dirname($_SERVER['SCRIPT_FILENAME'])));
define('APPLICATION_PATH', ROOT_PATH . '/application');

// Define application environment
define('APPLICATION_ENV', 'production');

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
	realpath(ROOT_PATH . '/library'),
	get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
	APPLICATION_ENV,
	APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap(array('base', 'autoload', 'config', 'ndebug', 'cache', 'db', 'locale'));

$_c = new Model_Connectors();
$_u = new Model_Users();
$config = Zend_Registry::get('config');
$_g = new App_GoodDataService($config->gooddata->username, $config->gooddata->password);
$_f = new App_Connector_Facebook();

//@TODO other connectors?
$connector = $_c->fetchRow(array('id=?' => 1));
foreach($_u->fetchAll(array('export=1', 'idGD IS NULL')) as $user) {
	echo "****************************\n***  Export: ".$user->email."\n";

	$idGD = $_g->createProject($config->app->name.' - '.$user->email, $connector->templateUri);
	if($idGD) {
		$user->idGD = $idGD;
		$user->save();
		$_f->userHasProject($user->id);
	}
}