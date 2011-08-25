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
	echo $e->getUsageMessage();
	exit;
}


$_p = new Model_Pages();
$_l = new Model_Likes();


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
	$since = date('Y-m-d', strtotime('-4 days'));
}

$until = $opts->getOption('until');
if ($until) {
	$until = date('Y-m-d', strtotime($until));
} else {
	$until = date('Y-m-d');
}

$config = Zend_Registry::get('config');

foreach($pages as $page) {
	$insights = new App_FacebookInsights($page);
	try {
		$data = $insights->getData($since, $until);
	} catch(App_FacebookException $e) {
		App_Debug::send('Error for page '.$page->id. '('.$page->name.'), interval: '.$since.'-'.$until.' - '.$e->getMessage()."\n");

		echo "There was an error during talking to Facebook API. Try again please.\n";
		continue;
	}

	if (isset($data['lifetime'])) {
		$lifetime = $data['lifetime'];
		$data = array_diff_key($data, array('lifetime'));

		$monthDay = date('Y-m-01', strtotime($lifetime['date']));
		$l = $_l->fetchRow(array('idPage=?' => $page->id, 'month=?' => $monthDay));
		if (!$l) {
			$lId = $_l->insert(array(
				'idPage'		=> $page->id,
				'month'			=> $monthDay,
				'date'			=> $lifetime['date'],
				'male'			=> isset($lifetime['likesMale']) ? $lifetime['likesMale'] : 0,
				'female'		=> isset($lifetime['likesFemale']) ? $lifetime['likesFemale'] : 0,
				'unknownSex'	=> isset($lifetime['likesUnknownSex']) ? $lifetime['likesUnknownSex'] : 0
			));
			$l = $_l->fetchRow(array('id=?' => $lId));
		} else {
			if ($l->date <= $lifetime['date']) {
				$l->date = $lifetime['date'];
				$l->male = isset($lifetime['likesMale']) ? $lifetime['likesMale'] : 0;
				$l->female = isset($lifetime['likesFemale']) ? $lifetime['likesFemale'] : 0;
				$l->unknownSex = isset($lifetime['likesUnknownSex']) ? $lifetime['likesUnknownSex'] : 0;
				$l->save();
			}
		}

		if (isset($lifetime['countries'])) {
			$l->addCountries($page->id, $lifetime['countries'], $lifetime['date']);
		}
		if (isset($lifetime['cities'])) {
			$l->addCities($page->id, $lifetime['cities'], $lifetime['date']);
		}
		if (isset($lifetime['age'])) {
			$l->addAge($page->id, $lifetime['age'], $lifetime['date']);
		}
	}
	

	$_d = new Model_Days();
	foreach($data as $date => $values) {
		$age = isset($values['age']) ? $values['age'] : array();
		$activeUsersCity = isset($values['activeUsersCity']) ? $values['activeUsersCity'] : array();
		$activeUsersCountry = isset($values['activeUsersCountry']) ? $values['activeUsersCountry'] : array();
		$internalReferrals = isset($values['internalReferrals']) ? $values['internalReferrals'] : array();
		$externalReferrals = isset($values['externalReferrals']) ? $values['externalReferrals'] : array();

		$day = $_d->add(array_merge(
			array('date' => $date, 'idPage' => $page->id),
			array_diff_key($values, array(
				'age' => null,
				'activeUsersCity' => null,
				'activeUsersCountry' => null,
				'internalReferrals' => null,
				'externalReferrals' => null
			))
		));
		$day->addAge($page->id, $age);
		$day->addCities($page->id, $activeUsersCity);
		$day->addCountries($page->id, $activeUsersCountry);
		$day->addReferrals($page->id, $internalReferrals, $externalReferrals);
	}
}