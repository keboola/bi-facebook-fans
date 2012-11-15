<?php
define('ROOT_PATH', dirname(dirname($_SERVER['SCRIPT_FILENAME'])));
define('APPLICATION_PATH', ROOT_PATH . '/application');
define('TMP_PATH', ini_get('upload_tmp_dir'));
define('EXEC_PATH', ROOT_PATH . '/exec');
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));
set_include_path(implode(PATH_SEPARATOR, array(realpath(ROOT_PATH . '/library'), get_include_path(),)));

require_once 'Zend/Application.php';

$application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
$application->bootstrap();

$config = Zend_Registry::get('config');

$_a = new Model_Accounts();
$_u = new Model_Users();
//$_f = new Model_Friends();
$_s = new Model_StatusMessages();
//$_pu = new Model_PagesUsers();
//$_o = new Model_Objects();
//$_so = new Model_StatusMessagesObjects();
//$_g = new App_GoodData($config->gooddata->username, $config->gooddata->password);

$since = date('Y-m-d', strtotime('-30 days'));
$until = date('Y-m-d');

// IMPORT

foreach($_u->fetchAll() as $user) {
	
	$fbImport = new App_Facebook_Import($user);

	$lastDate = $_s->getAdapter()->fetchOne("SELECT MAX(DATE(datetime)) as maxDate FROM bi_statusMessages WHERE idUser=$user->id");

	if ($lastDate != null && $lastDate < $since) {
		$since = $lastDate;
	}

	echo "Since: " . $since . "\n";
	echo "Until: " . $until . "\n";		
	
	// User's friends
	$fbImport->importFriends();

	// User's feed
	$fbImport->importFeed($since, $until);
	
	$fbImport->influence();

	//@TODO: import user's Likes?

}

// Export
foreach ($_a->fetchAll() as $account) {

	if ($account->idGdProject) {
		$fgd = new App_GoodDataExport($account->idGdProject, $account-Id, $config);
		$fgd->loadData(true);
	}
}