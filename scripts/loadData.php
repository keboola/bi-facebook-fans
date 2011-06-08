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
$facebook = new App_Facebook($config->facebook->pageId, $config->facebook->appToken);

$_d = new Model_Days();
$days = array();

$results = $facebook->call('insights/page_views', 'day', '2010-09-01', '2010-10-01');
if (isset($results['values'])) {
	foreach($results['values'] as $v) {
		$days[strtotime($v['end_time'])]['visits'] = $v['value'];
	}
}

$results = $facebook->call('insights/page_like_adds', 'day', '2010-09-01', '2010-10-01');
if (isset($results['values'])) {
	foreach($results['values'] as $v) {
		$days[strtotime($v['end_time'])]['likes'] = $v['value'];
	}
}

foreach($days as $date => $values) {
	$_d->add($date, $values['visits'], $values['likes']);
}

die();

$gdParams = 'RetrieveProject(fileName="../Keboola.pid");';

switch($opts->getOption('export')) {
	case 'visits':
		$gdParams .= 'UseFacebookInsights(startDate="2011-01-01",endDate="2011-01-30", baseUrl="https://graph.facebook.com/'.$config->facebook->pageId.'/insights/page_views/day", configFile="config.xml",authToken="'.$token.'");';
	break;
	default:
		echo $opts->getUsageMessage();
}

$gdParams .= 'Dump(csvFile="mydata.csv");';
echo $token."\n";die();
system(APPLICATION_PATH."/../../../../../cli/bin/gdi.sh -u ".$config->gooddata->username." -p ".$config->gooddata->password." -e ".escapeshellarg($gdParams));
unlink(APPLICATION_PATH.'/../../*.log');