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
$_uc = new Model_UsersToConnectors();
$_g = new App_GoodDataService($config->gooddata->username, $config->gooddata->password);


//Trial or paid period ended
foreach($_uc->fetchAll(array('paidUntil < ?' => date('Y-m-d'))) as $uc) {
	$user = $uc->findParentRow('Model_Users');

	$html = new Zend_View();
	$html->setScriptPath(APPLICATION_PATH.'/views/emails/');
	$bodyHtml = $html->render("unpaid.phtml");

	$m = new Zend_Mail('utf8');
	$m->setFrom($config->app->email);
	$m->addTo($user->email);
	$m->setSubject($config->app->name.' error');
	$m->setBodyHtml($html);

	$m->send();
}