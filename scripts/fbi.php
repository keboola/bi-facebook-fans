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
	'page|p-i' => 'Id of page in db',
	'since|s-w' => 'Start date of export',
	'until|u-w' => 'End date of export'
));
$opts->setHelp(array(
	'p' => 'Id of page in db',
	's' => 'Start date of export in yyyymmdd format',
	'u' => 'End date of export in yyyymmdd format'
));
try {
	$opts->parse();
} catch (Zend_Console_Getopt_Exception $e) {
	echo $opts->getUsageMessage();
	exit;
}


$_p = new Model_Pages();

$p = $opts->getOption('page');
if ($p) {
	$page = $_p->fetchRow(array('id=?' => $p));
	if (!$page) {
		echo "You have wrong page id.\n";
		exit;
	}
	$pages = array($page);
} else {
	// get all pages which should fetch the data
	$pages = $_p->fetchAll(array('isActive=?' => 1));
}

$since = $opts->getOption('since');
if ($since) {
	$since = date('Y-m-d', strtotime($since));
} else {
	$since = strtotime(date('Y-m-d 00:00:00', strtotime('-4 days')));
}

$until = $opts->getOption('until');
if ($until) {
	$until = date('Y-m-d', strtotime($until));
} else {
	$until = strtotime(date('Y-m-d 23:59:59', strtotime('-1 day')));
}

$config = Zend_Registry::get('config');

foreach($pages as $page) {
	try {
		$_i = new App_Import($page);
		$result = $_i->run($since, $until);
		if ($result && !$page->isImported) {
			$page->isImported = 1;
			$page->save();
		}
	} catch(App_FacebookException $e) {
		App_Debug::send('Error for page '.$this->_page->id. '('.$this->_page->name.'), interval: '.$since.'-'.$until.' - '.$e->getMessage()."\n");

		echo "There was an error during talking to Facebook API. Try again please.\n";
		continue;
	}
}