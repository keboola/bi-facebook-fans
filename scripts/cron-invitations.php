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

$config = Zend_Registry::get('config');
$_i = new Model_Invitations();
$_g = new App_GoodData($config->gooddata->username, $config->gooddata->password);


foreach($_i->fetchAll(array('isSent=0')) as $i) {
	$user = $i->findParentRow('Model_UsersToConnectors')->findParentRow('Model_Users');
	if ($user->idGD) {
		echo "****************************\n***  Invitation: ".$user->idGD." - ".$i->email."\n";
		$sent = $_g->inviteUser($user->idGD, $i->email, $i->role, $i->text);
		if ($sent) {
			$i->isSent = TRUE;
			//$i->text = null;
			$i->save();
		}
	}
}