<?php
define('ROOT_PATH', dirname(dirname($_SERVER['SCRIPT_FILENAME'])));
define('APPLICATION_PATH', ROOT_PATH . '/application');

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
			realpath(APPLICATION_PATH . '/../library'),
			get_include_path(),
		)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
	'production',
	APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap();

// Setup console input
$opts = new Zend_Console_Getopt(array(
		'page|p=i' => 'Id of page in db',
		'table|t-s' => 'table option, with required string parameter',
		'setup|s-i' => 'setup datasets in GoodData',
		'load|l-i' => 'load data to datasets in GoodData',
	));
$opts->setHelp(array(
		'p' => 'Id of page in db',
		't' => 'Name of the table to export.',
		's' => 'Setup datasets in GoodData',
		'l' => 'Load data to datasets in GoodData'
	));
try {
	$opts->parse();
} catch (Zend_Console_Getopt_Exception $e) {
	echo $e->getUsageMessage();
	exit;
}

$p = $opts->getOption('page');
$_p = new Model_Pages();
if ($p) {
	$page = $_p->fetchRow(array('id=?' => $p));
	if (!$page) {
		echo "You have wrong page id.\n";
		exit;
	}
	$pages = array($page);
} else {
	// get all pages which should fetch the data
	if ($opts->getOption('setup')) {
		$pages = $_p->fetchAll(array('isActive=?' => 1, 'isInGD=?' => 0));
	} else if ($opts->getOption('load')) {
		$pages = $_p->fetchAll(array('isActive=?' => 1, 'isInGD=?' => 1));
	} else {
		echo $e->getUsageMessage();
		exit;
	}
}

$config = Zend_Registry::get('config');

foreach ($pages as $page) {
	$fgd = new App_FacebookGoodData($config->gooddata->username, $config->gooddata->password, $page->idProject, $page->id);

	if ($opts->getOption('setup')) {
		$fgd->setup();

		$page->isInGD = 1;
		$page->save();

	} elseif ($opts->getOption('load')) {
		$fgd->loadData();

	} else {
		switch ($opts->getOption('table')) {
			case 'age':
				echo $fgd->dumpAge(true);
				break;
			case 'cities':
				echo $fgd->dumpCities(true);
				break;
			case 'days':
				echo $fgd->dumpDays(true);
				break;
			case 'daysCountries':
				echo $fgd->dumpDaysCountries(true);
				break;
			case 'likes':
				echo $fgd->dumpLikes(true);
				break;
			case 'likesCountries':
				echo $fgd->dumpLikesCountries(true);
				break;
			case 'rDaysAge':
				echo $fgd->dumpDaysAge(true);
				break;
			case 'rDaysCities':
				echo $fgd->dumpDaysCities(true);
				break;
			case 'rDaysReferrals':
				echo $fgd->dumpDaysReferrals(true);
				break;
			case 'rLikesAge':
				echo $fgd->dumpLikesAge(true);
				break;
			case 'rLikesCities':
				echo $fgd->dumpLikesCities(true);
				break;
			case 'referrals':
				echo $fgd->dumpReferrals(true);
				break;
			default:
				echo $opts->getUsageMessage();
		}
	}
}
