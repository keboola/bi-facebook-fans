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
$p = $opts->getOption('page');
if ($p) {
	$page = $_p->fetchRow(array('id=?' => $p));
	if (!$page) {
		echo "You have wrong page id.\n";
		exit;
	}
	$pages = array($page);
} else {
	$pages = $_p->fetchAll();
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
		$message = 'Error for page '.$page->id. '('.$page->name.'), interval: '.$since.'-'.$until.' - '.$e->getMessage()."\n";

		$mail = new Zend_Mail('utf-8');
		$mail->setFrom($config->app->email, 'Facebook-GoodData Connector');
		$mail->addTo($config->app->admin);
		$mail->setBodyText($message);
		$mail->setSubject('Connector error');
		$mail->send();

		echo 'There was an error during talking to Facebook API. Try again please.';
		continue;
	}

	if (isset($data['lifetime'])) {
		$lifetime = $data['lifetime'];
		$data = array_diff_key($data, array('lifetime'));

		if ($page->likesDate < $lifetime['date'] && isset($lifetime['likesMale']) && isset($lifetime['likesFemale'])
			&& isset($lifetime['likesUnknownSex'])) {
			$page->likesDate = $lifetime['date'];
			$page->likesMale = $lifetime['likesMale'];
			$page->likesFemale = $lifetime['likesFemale'];
			$page->likesUnknownSex = $lifetime['likesUnknownSex'];
			$page->save();
		}

		if (isset($lifetime['countries'])) {
			$page->addCountries($lifetime['countries'], $lifetime['date']);
		}
		if (isset($lifetime['cities'])) {
			$page->addCities($lifetime['cities'], $lifetime['date']);
		}
		if (isset($lifetime['age'])) {
			$page->addAge($lifetime['age'], $lifetime['date']);
		}
	}

	$_d = new Model_Days();
	foreach($data as $date => $values) {
		$age = isset($values['age']) ? $values['age'] : array();
		$activeUsersCity = isset($values['activeUsersCity']) ? $values['activeUsersCity'] : array();
		$activeUsersCountry = isset($values['activeUsersCountry']) ? $values['activeUsersCountry'] : array();
		$internalReferrals = isset($values['internalReferrals']) ? $values['internalReferrals'] : array();
		$externalReferrals = isset($values['externalReferrals']) ? $values['externalReferrals'] : array();

		$id = $_d->add(array_merge(
			array('date' => $date, 'idPage' => $page->id),
			array_diff_key($values, array(
				'age' => null,
				'activeUsersCity' => null,
				'activeUsersCountry' => null,
				'internalReferrals' => null,
				'externalReferrals' => null
			))
		));
		$day = $_d->fetchRow(array('id=?' => $id));
		$day->addAge($age);
		$day->addCities($activeUsersCity);
		$day->addCountries($activeUsersCountry);
		$day->addReferrals($internalReferrals, $externalReferrals);
	}
}