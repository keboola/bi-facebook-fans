<?php
define('ROOT_PATH', dirname(dirname($_SERVER['SCRIPT_FILENAME'])));
define('APPLICATION_PATH', ROOT_PATH . '/application');
define('APPLICATION_ENV', 'production');
set_include_path(implode(PATH_SEPARATOR, array(
	realpath(ROOT_PATH . '/library'),
	get_include_path(),
)));
require_once 'Zend/Application.php';
$application = new Zend_Application(
	APPLICATION_ENV,
	APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap(array('base', 'autoload', 'config', 'ndebug', 'cache', 'db', 'locale'));


$config = Zend_Registry::get('config');
$_c = new Model_Connectors();
$_u = new Model_Users();
$_g = new App_GoodData($config->gooddata->username, $config->gooddata->password);
$_uc = new Model_UsersToConnectors();


$_f = new App_Connector_Facebook();
foreach($_uc->fetchAll(array('idConnector=?' => 1, 'idGD IS NULL')) as $uc) {
	$connector = $uc->findParentRow('Model_Connectors');
	$user = $uc->findParentRow('Model_Users');
	
	echo "****************************\n***  Export: ".$user->email."\n";

	$idGD = $_g->createProject($config->app->projectName.' - '.$user->email, $connector->templateUri);
	if($idGD) {
		$uc->idGD = $idGD;
		$uc->save();
		$_f->userHasProject($user->id);
	}
}


/***********************************************************************************************************************
 * @TODO temporary creation of separate projects for other connectors
 */



//Google Analytics
$_c = new App_Connector_GoogleAnalytics();
foreach($_uc->fetchAll(array('idConnector=?' => 2, 'idGD IS NULL')) as $uc) {
	$connector = $uc->findParentRow('Model_Connectors');
	$user = $uc->findParentRow('Model_Users');

	echo "****************************\n***  Export: ".$user->email."\n";

	$idGD = $_g->createProject($connector->name.' - '.$user->email);
	if($idGD) {
		$uc->idGD = $idGD;
		$uc->save();

		if($_g->cloneProject($connector->templateUri, $idGD)) {
			$_c->userHasProject($user->id);
		} else {
			echo "*** Project cloning failed ... \n";
		}
	}
}


//Twitter
$_c = new App_Connector_Twitter();
foreach($_uc->fetchAll(array('idConnector=?' => 3, 'idGD IS NULL')) as $uc) {
	$connector = $uc->findParentRow('Model_Connectors');
	$user = $uc->findParentRow('Model_Users');
	$idGD = $_g->createProject($connector->name.' - '.$user->email);
	if($idGD) {
		$uc->idGD = $idGD;
		$uc->save();

		if($_g->cloneProject($connector->templateUri, $idGD)) {
			$_c->userHasProject($user->id);
		}
	}
}