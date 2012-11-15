<?php
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('APPLICATION_PATH', ROOT_PATH . '/application');
set_include_path(implode(PATH_SEPARATOR, array(realpath(ROOT_PATH . '/library'), get_include_path())));
require_once 'Zend/Application.php';
$application = new Zend_Application('application', APPLICATION_PATH . '/configs/application.ini');
$application->bootstrap();

$opts = new Zend_Console_Getopt(array(
	'id|i-i' => 'Id of account in db',
	'since|s-w' => 'Start date of export',
	'until|u-w' => 'End date of export'
));
$opts->setHelp(array(
	'i' => 'Id of account in db',
	's' => 'Start date of export in yyyymmdd format',
	'u' => 'End date of export in yyyymmdd format'
));
try {
	$opts->parse();
} catch (Zend_Console_Getopt_Exception $e) {
	echo $opts->getUsageMessage();
	exit;
}


$_a = new Model_Accounts();

$id = $opts->getOption('i');
if ($id) {
	$account = $_a->fetchRow(array('id=?' => $id));
	if (!$account) {
		echo "You have wrong account id.\n";
		exit;
	}
	$accounts = array($account);
} else {
	// get all accounts which should fetch the data
	$accounts = $_a->fetchAll(array('import=?' => 1));
}

$since = $opts->getOption('since');
if (!$since) {
	$since = '-4 days';
}
$since = date('Y-m-d', strtotime($since));

$until = $opts->getOption('until');
if (!$until) {
	$until = 'now';
}
$until = date('Y-m-d', strtotime($until));

$config = Zend_Registry::get('config');

foreach($accounts as $account) {
	try {
		echo "****************************\n***  ".$account->name."\n";
		$i = new App_FacebookImport($account);
		$i->savePosts($since, $until);
		$i->saveInsights($since, $until);

		echo "\n\n";
	} catch(App_FacebookException $e) {
		echo $e->getMessage()."\n";
		continue;
	}
}