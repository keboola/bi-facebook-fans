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


// Setup console input
$opts = new Zend_Console_Getopt(array(
	'page|p=i' => 'Id of page in db',
	'since|s=w' => 'Start date of export',
	'until|u=w' => 'End date of export'
));
$opts->setHelp(array(
	'p' => 'Id of page in db',
	's' => 'Start date of export in yyyymmdd format',
	'u' => 'End date of export in yyyymmdd format'
));
try {
	$opts->parse();
} catch (Zend_Console_Getopt_Exception $e) {
	echo $e->getUsageMessage();
	exit;
}

$p = $opts->getOption('page');
if ($p) {
	$_p = new Model_Pages();
	$page = $_p->fetchRow(array('id=?' => $p));
	if (!$page) {
		echo "You have wrong page id.\n";
		exit;
	}
} else {
	echo $opts->getUsageMessage();
	exit;
}

$since = $opts->getOption('since');
if ($since) {
	$since = date('Y-m-d', strtotime($since));
} else {
	echo $opts->getUsageMessage();
	exit;
}

$until = $opts->getOption('until');
if ($until) {
	$until = date('Y-m-d', strtotime($until));
} else {
	echo $opts->getUsageMessage();
	exit;
}

$config = Zend_Registry::get('config');
$insights = new App_FacebookInsights($page);
try {
	$data = $insights->getData($since, $until);
} catch(App_FacebookException $e) {
	echo $e->getMessage()."\n";
	die();
}


$_d = new Model_Days();
foreach($data as $date => $values) {
	$activeUsersCountry = $values['activeUsersCountry'];
	$internalReferrals = $values['internalReferrals'];
	$externalReferrals = $values['externalReferrals'];

	$id = $_d->add(array_merge(
		array('date' => $date, 'idPage' => $page->id),
		array_diff_key($values, array('activeUsersCountry' => null, 'internalReferrals' => null, 'externalReferrals' => null))
	));
	$day = $_d->fetchRow(array('id=?' => $id));
	$day->addUserCountries($activeUsersCountry);
	$day->addReferrals($internalReferrals, $externalReferrals);
}