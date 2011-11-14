<?php
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
define('TMP_PATH', realpath(APPLICATION_PATH.'/../tmp/'));

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

// Setup console input
$opts = new Zend_Console_Getopt(array(
		'create|c-s' => 'Create project with name',
		'dashboard|d-s' => 'Copy dashboard',
		'invite|i-s' => 'Invite user',
		'project|p-s' => 'GoodData Project id'
	));
$opts->setHelp(array(
		'c' => 'Create project with name. You have to specify email of user who will be invited to the project.',
		'd' => 'Copy dashboard to given project.',
		'i' => 'Invite user',
		'p' => 'GoodData Project id',
	));
try {
	$opts->parse();
} catch (Zend_Console_Getopt_Exception $e) {
	echo $e->getUsageMessage();
	exit;
}



$config = Zend_Registry::get('config');
$gds = new App_GoodDataService($config->gooddata->username, $config->gooddata->password);

if ($opts->getOption('create')) {

	echo $gds->createProject($opts->getOption('create'));

} else if ($opts->getOption('invite') && $opts->getOption('project')) {

	echo $gds->inviteUser($opts->getOption('project'), $opts->getOption('invite'));

} else if ($opts->getOption('dashboard') && $opts->getOption('project')) {

	$_c = new Model_Connectors();
	$template = $_c->fetchRow(array('id=?' => $opts->getOption('dashboard')));
	if($template) {
		echo $gds->copyMetaData($template->dashboard, $template->idGD, $opts->getOption('project'));
	}

}
echo "\n\n";