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
	'export|e=w' => 'what to export option: visits'
));
$opts->setHelp(array(
	'e' => 'What to export: visits'
));
try {
	$opts->parse();
} catch (Zend_Console_Getopt_Exception $e) {
	echo $e->getUsageMessage();
	exit;
}

$config = Zend_Registry::get('config');
$insights = new App_FacebookInsights($config->facebook->pageId, $config->facebook->appToken);


$data = array_merge(
	$insights->getData('2010-12-01', '2011-01-01'),
	$insights->getData('2011-01-01', '2011-02-01'),
	$insights->getData('2011-02-01', '2011-03-01'),
	$insights->getData('2011-03-01', '2011-04-01'),
	$insights->getData('2011-04-01', '2011-05-01'),
	$insights->getData('2011-05-01', '2011-06-01'),
	$insights->getData('2011-06-01', '2011-06-09')
);

$_d = new Model_Days();
foreach($data as $date => $values) {
	$activeUsersCountry = $values['activeUsersCountry'];
	$internalReferrals = $values['internalReferrals'];
	$externalReferrals = $values['externalReferrals'];

	$id = $_d->add(array_merge(
		array('date' => $date),
		array_diff_key($values, array('activeUsersCountry' => null, 'internalReferrals' => null, 'externalReferrals' => null))
	));
	$day = $_d->fetchRow(array('id=?' => $id));
	$day->addUserCountries($activeUsersCountry);
	$day->addReferrals($internalReferrals, $externalReferrals);
}